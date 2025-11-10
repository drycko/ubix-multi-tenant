<?php

namespace App\Http\Controllers\Central;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\Request;
use App\Traits\LogsAdminActivity;


class DashboardController extends Controller
{
    use LogsAdminActivity;
  
    public function __construct()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        $this->middleware('auth:web');
        // TODO: Add permission middleware when central permissions are implemented
        $this->middleware('permission:view dashboard')->only(['index', 'show']);
        $this->middleware('permission:view analytics')->only(['stats']);
        $this->middleware('permission:view central knowledge base')->only(['knowledgeBase']);
    }
    
    public function index()
    {

        // get all tenants
        $tenants = Tenant::all();
        $totalTenants = $tenants->count();
        $activeTenants = $tenants->where('is_active', true)->count();
        // make sure tenant has an active subscription
        $activeTenantsWithSubscription = $tenants->filter(function ($tenant) {
            return $tenant->subscriptions()->where('status', 'active')->exists();
        })->count();
        $trialingTenants = $tenants->filter(function ($tenant) {
            return $tenant->subscriptions()->where('status', 'trial')->exists();
        })->count();

        foreach ($tenants as $tenant) {
            $tenant->database = $tenant->tenancy_db_name; // in case tenancy decide to rename these attributes in future
        }

        // get unpaid invoices count
        $unpaidInvoicesCount = SubscriptionInvoice::whereIn('status', ['pending', 'partially_paid', 'overdue'])->count();

        $stats = [
			'total_tenants' => $totalTenants,
			'active_tenants' => $activeTenantsWithSubscription,
            'trialing_tenants' => $trialingTenants,
            'invoiced_tenants' => $unpaidInvoicesCount,
			// Add more stats as needed
		];
        
        return view('central.dashboard', compact('stats', 'tenants'));
    }

    // public function settings()
    // {
    //     // use the central database connection from here because I am in the central app
    //     config(['database.connections.tenant' => config('database.connections.central')]);
        
    //     return view('central.settings');
    // }

    public function stats()
    {
        
        // Example stats data
        $stats = [
            'total_tenants' => Tenant::count(),
            // Add more stats as needed
        ];

        return view('central.stats', compact('stats'));
    }

    /**
     * Display the central knowledge base
     */
    public function knowledgeBase()
    {
        $knowledgeBasePath = base_path('CENTRAL_KNOWLEDGE_BASE.md');
        
        if (!file_exists($knowledgeBasePath)) {
            abort(404, 'Central knowledge base not found');
        }
        
        $content = file_get_contents($knowledgeBasePath);
        
        return view('central.knowledge-base', compact('content'));
    }
}