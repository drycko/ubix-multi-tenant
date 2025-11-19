<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use App\Models\AdminActivity;
use App\Models\User;
use App\Traits\LogsAdminActivity;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    use LogsAdminActivity;

    public function __construct()
    {
        config(['database.connections.tenant' => config('database.connections.central')]);
        $this->middleware(['auth:web']);
        $this->middleware('permission:view reports')->only(['index', 'tenants', 'subscriptions', 'financial', 'userActivity']);
    }

    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        // Log activity
        $this->logAdminActivity(
            'view',
            'reports',
            0,
            'Viewed Central Reports Dashboard'
        );

        // Get summary statistics for the dashboard
        $stats = $this->getReportStats();
        
        // Get recent activity
        $recentActivity = AdminActivity::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('central.reports.index', compact('stats', 'recentActivity'));
    }

    /**
     * Show tenant reports
     */
    public function tenants(Request $request)
    {
        $this->logAdminActivity(
            'view',
            'reports',
            0,
            'Viewed Tenant Reports'
        );

        $query = Tenant::with(['subscriptions', 'invoices']);
        
        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('status')) {
            // Filter by tenant status if you have a status field
            $query->where('status', $request->status);
        }

        $tenants = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Generate summary statistics
        $summary = $this->getTenantsSummary($request);

        return view('central.reports.tenants', compact(
            'tenants', 
            'summary'
        ));
    }

    /**
     * Show subscription reports
     */
    public function subscriptions(Request $request)
    {
        $this->logAdminActivity(
            'view',
            'reports',
            0,
            'Viewed Subscription Reports'
        );

        $query = Subscription::with(['tenant', 'plan']);
        
        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get filter options
        $plans = SubscriptionPlan::all();
        $statuses = ['active', 'inactive', 'cancelled', 'expired', 'trial'];

        // Generate summary statistics
        $summary = $this->getSubscriptionsSummary($request);

        return view('central.reports.subscriptions', compact(
            'subscriptions', 
            'plans', 
            'statuses', 
            'summary'
        ));
    }

    /**
     * Show financial reports
     */
    public function financial(Request $request)
    {
        $this->logAdminActivity(
            'view',
            'reports',
            0,
            'Viewed Financial Reports'
        );

        // Revenue by period
        $paymentsQuery = SubscriptionPayment::with('subscription.tenant');
        
        if ($request->filled('date_from')) {
            $paymentsQuery->whereDate('payment_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $paymentsQuery->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $paymentsQuery->orderBy('payment_date', 'desc')->paginate(20);
        
        // Generate financial summary
        $summary = $this->getFinancialSummary($request);
        
        // Revenue trends
        $revenueTrends = $this->getRevenueTrends($request);

        return view('central.reports.financial', compact(
            'payments', 
            'summary', 
            'revenueTrends'
        ));
    }

    /**
     * Show admin activity reports
     */
    public function userActivity(Request $request)
    {
        $this->logAdminActivity(
            'view',
            'reports',
            0,
            'Viewed Admin Activity Reports'
        );

        $query = AdminActivity::with('user');
        
        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('user_id')) {
            $query->where('admin_id', $request->user_id);
        }
        
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get users for filter
        $users = User::orderBy('name')->get();
        
        // Activity summary
        $summary = $this->getUserActivitySummary($request);

        return view('central.reports.user-activity', compact(
            'activities', 
            'users', 
            'summary'
        ));
    }

    /**
     * Get general report statistics
     */
    private function getReportStats()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        return [
            'total_tenants' => Tenant::count(),
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'total_revenue' => SubscriptionPayment::where('status', 'completed')->sum('amount'),
            'tenants_this_month' => Tenant::whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count(),
            'revenue_this_month' => SubscriptionPayment::where('status', 'completed')
                ->whereMonth('payment_date', $currentMonth)
                ->whereYear('payment_date', $currentYear)
                ->sum('amount'),
            'active_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'total_invoices' => SubscriptionInvoice::count(),
            'pending_invoices' => SubscriptionInvoice::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get tenant summary
     */
    private function getTenantsSummary($request)
    {
        $query = Tenant::query();
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $totalTenants = (clone $query)->count();
        
        // Get tenants with active subscriptions
        $activeSubscriptionTenants = Tenant::whereHas('subscriptions', function($q) {
            $q->where('status', 'active');
        });
        
        if ($request->filled('date_from')) {
            $activeSubscriptionTenants->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $activeSubscriptionTenants->whereDate('created_at', '<=', $request->date_to);
        }
        
        $activeSubscriptionTenants = $activeSubscriptionTenants->count();

        return [
            'total_tenants' => $totalTenants,
            'active_subscription_tenants' => $activeSubscriptionTenants,
            'trial_tenants' => Tenant::whereHas('subscriptions', function($q) {
                $q->where('status', 'trial');
            })->count(),
            'inactive_tenants' => $totalTenants - $activeSubscriptionTenants,
        ];
    }

    /**
     * Get subscription summary
     */
    private function getSubscriptionsSummary($request)
    {
        $query = Subscription::query();
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return [
            'total_subscriptions' => (clone $query)->count(),
            'active_subscriptions' => (clone $query)->where('status', 'active')->count(),
            'trial_subscriptions' => (clone $query)->where('status', 'trial')->count(),
            'cancelled_subscriptions' => (clone $query)->where('status', 'cancelled')->count(),
            'expired_subscriptions' => (clone $query)->where('status', 'expired')->count(),
        ];
    }

    /**
     * Get financial summary
     */
    private function getFinancialSummary($request)
    {
        $query = SubscriptionPayment::where('status', 'completed');
        
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        return [
            'total_revenue' => $query->sum('amount'),
            'total_payments' => $query->count(),
            'avg_payment_value' => $query->avg('amount'),
            'pending_payments' => SubscriptionPayment::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get admin activity summary
     */
    private function getUserActivitySummary($request)
    {
        $query = AdminActivity::query();
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return [
            'total_activities' => $query->count(),
            'unique_users' => $query->distinct('admin_id')->count('admin_id'),
            'top_actions' => $query->select('activity_type', DB::raw('COUNT(*) as count'))
                ->groupBy('activity_type')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('count', 'activity_type'),
            'activities_today' => $query->whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Get revenue trends
     */
    private function getRevenueTrends($request)
    {
        $dateFrom = $request->date_from ?? now()->subDays(30)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        return SubscriptionPayment::where('status', 'completed')
            ->whereDate('payment_date', '>=', $dateFrom)
            ->whereDate('payment_date', '<=', $dateTo)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Export reports to CSV
     */
    public function export(Request $request, $type)
    {
        $request->validate([
            'report_type' => 'required|in:tenants,subscriptions,financial,user_activity'
        ]);

        $this->logAdminActivity('export', 'reports', 0, 'Exported Report: ' . $request->report_type);

        $data = [];
        $filename = '';

        switch ($request->report_type) {
            case 'tenants':
                $data = $this->getTenantsExportData($request);
                $filename = 'tenants-report-' . date('Y-m-d') . '.csv';
                break;

            case 'subscriptions':
                $data = $this->getSubscriptionsExportData($request);
                $filename = 'subscriptions-report-' . date('Y-m-d') . '.csv';
                break;

            case 'financial':
                $data = $this->getFinancialExportData($request);
                $filename = 'financial-report-' . date('Y-m-d') . '.csv';
                break;

            case 'user_activity':
                $data = $this->getUserActivityExportData($request);
                $filename = 'user-activity-report-' . date('Y-m-d') . '.csv';
                break;
        }

        if ($type === 'csv') {
            return $this->exportToCsv($data, $filename, $request->report_type);
        }

        return response()->json(['error' => 'Export type not supported'], 400);
    }

    /**
     * Get tenants export data
     */
    private function getTenantsExportData($request)
    {
        $query = Tenant::with(['subscriptions', 'subscriptionInvoices']);
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->get();
    }

    /**
     * Get subscriptions export data
     */
    private function getSubscriptionsExportData($request)
    {
        $query = Subscription::with(['tenant', 'plan']);
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        return $query->get();
    }

    /**
     * Get financial export data
     */
    private function getFinancialExportData($request)
    {
        $query = SubscriptionPayment::with('subscription.tenant');
        
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        return $query->get();
    }

    /**
     * Get user activity export data
     */
    private function getUserActivityExportData($request)
    {
        $query = AdminActivity::with('user');
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('user_id')) {
            $query->where('admin_id', $request->user_id);
        }
        
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        return $query->get();
    }

    /**
     * Export data to CSV
     */
    protected function exportToCsv($data, $filename, $reportType)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            switch ($reportType) {
                case 'tenants':
                    fputcsv($file, ['ID', 'Name', 'Email', 'Domain', 'Total Subscriptions', 'Active Subscriptions', 'Total Invoices', 'Created At']);
                    foreach ($data as $tenant) {
                        fputcsv($file, [
                            $tenant->id,
                            $tenant->name,
                            $tenant->email,
                            $tenant->domains->first()->domain ?? 'N/A',
                            $tenant->subscriptions->count(),
                            $tenant->subscriptions->where('status', 'active')->count(),
                            $tenant->subscriptionInvoices->count(),
                            $tenant->created_at->format('Y-m-d H:i:s')
                        ]);
                    }
                    break;

                case 'subscriptions':
                    fputcsv($file, ['ID', 'Tenant', 'Plan', 'Status', 'Start Date', 'End Date', 'Amount', 'Created At']);
                    foreach ($data as $subscription) {
                        fputcsv($file, [
                            $subscription->id,
                            $subscription->tenant->name ?? 'N/A',
                            $subscription->plan->name ?? 'N/A',
                            $subscription->status,
                            $subscription->start_date ? \Carbon\Carbon::parse($subscription->start_date)->format('Y-m-d') : 'N/A',
                            $subscription->end_date ? \Carbon\Carbon::parse($subscription->end_date)->format('Y-m-d') : 'N/A',
                            $subscription->amount ?? 0,
                            $subscription->created_at->format('Y-m-d H:i:s')
                        ]);
                    }
                    break;

                case 'financial':
                    fputcsv($file, ['ID', 'Tenant', 'Subscription Plan', 'Amount', 'Status', 'Payment Method', 'Payment Date', 'Created At']);
                    foreach ($data as $payment) {
                        fputcsv($file, [
                            $payment->id,
                            $payment->subscription->tenant->name ?? 'N/A',
                            $payment->subscription->plan->name ?? 'N/A',
                            $payment->amount,
                            $payment->status,
                            $payment->payment_method ?? 'N/A',
                            $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') : 'N/A',
                            $payment->created_at->format('Y-m-d H:i:s')
                        ]);
                    }
                    break;

                case 'user_activity':
                    fputcsv($file, ['ID', 'Admin', 'Activity Type', 'Table Name', 'Record ID', 'Description', 'IP Address', 'Created At']);
                    foreach ($data as $activity) {
                        fputcsv($file, [
                            $activity->id,
                            $activity->user->name ?? 'System',
                            $activity->activity_type,
                            $activity->table_name ?? 'N/A',
                            $activity->record_id ?? 'N/A',
                            $activity->description,
                            $activity->ip_address ?? 'N/A',
                            $activity->created_at->format('Y-m-d H:i:s')
                        ]);
                    }
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
