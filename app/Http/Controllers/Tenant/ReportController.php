<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Booking;
use App\Models\Tenant\BookingInvoice;
use App\Models\Tenant\InvoicePayment;
use App\Models\Tenant\TenantUserActivity;
use App\Models\Tenant\Guest;
use App\Models\Tenant\Room;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\Property;
use App\Models\Tenant\User;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        $this->middleware(['auth:tenant']);
        $this->middleware('permission:view reports')->only(['index', 'advanced', 'bookings', 'financial']);
        // only if there is a current property
        // $this->middleware('property.exists')->only(['index', 'advanced', 'bookings', 'financial', 'occupancy']);
    }

    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        // Log activity for each rate created/updated
        $this->logTenantActivity(
            'view_user_activity_reports',
            'View Reports Dashboard',
            null,
            [
                'user_id' => auth()->id(),
            ]
        );

        // Get summary statistics for the dashboard
        $stats = $this->getReportStats();
        
        // Get recent activity
        $recentActivity = TenantUserActivity::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('tenant.reports.index', compact('stats', 'recentActivity'));
    }

    /**
     * Display advanced reports dashboard
     */
    public function advanced()
    {
        if (!current_property()) {
            return redirect()->route('tenant.reports.index')->with('warning', 'Please select a property to view advanced reports.');
        }

        $property = current_property();
        
        // Get report data
        $reportData = [
            'occupancy' => $this->getOccupancyReport(),
            'revenue' => $this->getRevenueReport(),
            'booking_source' => $this->getBookingSourceReport(),
            'room_performance' => $this->getRoomPerformanceReport()
        ];

        return view('tenant.reports.advanced', compact('reportData', 'property'));
    }

    /**
     * Show booking reports
     */
    public function bookings(Request $request)
    {
        // 
        $this->logTenantActivity(
            'view_booking_reports',
            'Viewed Booking Reports',
            null,
            [
                'user_id' => auth()->id(),
            ]
        );

        $query = Booking::with(['room.type', 'guests', 'property']);
        
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
        
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        
        if ($request->filled('room_type_id')) {
            $query->whereHas('room', function($q) use ($request) {
                $q->where('room_type_id', $request->room_type_id);
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get filter options
        $properties = Property::all();
        $roomTypes = RoomType::all();
        $statuses = ['confirmed', 'pending', 'cancelled', 'checked_in', 'checked_out'];

        // Generate summary statistics
        $summary = $this->getBookingsSummary($request);

        return view('tenant.reports.bookings', compact(
            'bookings', 
            'properties', 
            'roomTypes', 
            'statuses', 
            'summary'
        ));
    }

    /**
     * Show financial reports
     */
    public function financial(Request $request)
    {
        // log activity
        $this->logTenantActivity(
            'view_financial_reports',
            'Viewed Financial Reports',
            null,
            [
                'user_id' => auth()->id(),
            ]
        );

        // Revenue by period
        $revenueQuery = InvoicePayment::with('bookingInvoice.booking');
        
        if ($request->filled('date_from')) {
            $revenueQuery->whereDate('payment_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $revenueQuery->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $revenueQuery->orderBy('payment_date', 'desc')->paginate(20);
        
        // Generate financial summary
        $summary = $this->getFinancialSummary($request);
        
        // Revenue trends
        $revenueTrends = $this->getRevenueTrends($request);

        return view('tenant.reports.financial', compact(
            'payments', 
            'summary', 
            'revenueTrends'
        ));
    }

    /**
     * Show user activity reports
     */
    public function userActivity(Request $request)
    {
        // 
        $this->logTenantActivity(
            'view_user_activity_reports',
            'Viewed User Activity Reports',
            null,
            [
                'user_id' => auth()->id(),
            ]
        );

        $query = TenantUserActivity::with('user');
        
        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get users for filter
        $users = User::orderBy('name')->get();
        
        // Activity summary
        $summary = $this->getUserActivitySummary($request);

        return view('tenant.reports.user-activity', compact(
            'activities', 
            'users', 
            'summary'
        ));
    }

    /**
     * Show occupancy reports
     */
    public function occupancy(Request $request)
    {

        $this->logTenantActivity(
            'occupancy_reports',
            'Viewed Occupancy Reports',
            null,
            [
                'user_id' => auth()->id(),
            ]
        );

        $dateFrom = $request->date_from ?? now()->subDays(30)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        // Occupancy data
        $occupancyData = $this->getOccupancyData($dateFrom, $dateTo, $request->property_id);
        
        // Room performance
        $roomPerformance = $this->getRoomPerformanceData($dateFrom, $dateTo, $request->property_id);
        
        $properties = Property::all();

        return view('tenant.reports.occupancy', compact(
            'occupancyData', 
            'roomPerformance', 
            'properties'
        ));
    }

    /**
     * Get general report statistics
     */
    private function getReportStats()
    {
        $currentProperty = current_property();
        if (!$currentProperty) {
            return [];
        }
        
        return [
            'total_bookings' => Booking::where('property_id', $currentProperty->id)->count(),
            'total_revenue' => Booking::where('property_id', $currentProperty->id)->where('status', 'confirmed')->sum('total_amount'),
            'total_guests' => Guest::count(),
            'total_activities' => TenantUserActivity::count(),
            'bookings_this_month' => Booking::where('property_id', $currentProperty->id)->whereMonth('created_at', now()->month)->count(),
            'revenue_this_month' => Booking::where('property_id', $currentProperty->id)->where('status', 'confirmed')->whereMonth('arrival_date', now()->month)->sum('total_amount'),
            'active_users' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
            'avg_booking_value' => Booking::where('property_id', $currentProperty->id)->where('status', 'confirmed')->avg('total_amount'),
        ];
    }

    /**
     * Get booking summary
     */
    private function getBookingsSummary($request)
    {
        // do we have a property selected?
        if (!current_property()) {
            return [];
        }
        $query = Booking::where('property_id', current_property()->id);
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get basic counts
        $totalBookings = (clone $query)->count();
        $confirmedBookings = (clone $query)->where('status', 'confirmed')->count();
        $pendingBookings = (clone $query)->where('status', 'pending')->count();
        $cancelledBookings = (clone $query)->where('status', 'cancelled')->count();
        
        // Get average nights separately
        $avgNights = (clone $query)->selectRaw('AVG(DATEDIFF(departure_date, arrival_date)) as avg_nights')->value('avg_nights') ?? 0;
        
        // Get total guests separately to avoid GROUP BY conflicts
        $totalGuests = Booking::where('bookings.property_id', current_property()->id)
            ->join('booking_guests', 'bookings.id', '=', 'booking_guests.booking_id')
            ->whereNull('booking_guests.deleted_at')
            ->whereNull('bookings.deleted_at');
            
        // Apply the same date filters for guest count
        if ($request->filled('date_from')) {
            $totalGuests->whereDate('bookings.created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $totalGuests->whereDate('bookings.created_at', '<=', $request->date_to);
        }
        
        $totalGuests = $totalGuests->count();

        return [
            'total_bookings' => $totalBookings,
            'confirmed_bookings' => $confirmedBookings,
            'pending_bookings' => $pendingBookings,
            'cancelled_bookings' => $cancelledBookings,
            'avg_nights' => round($avgNights, 1),
            'total_guests' => $totalGuests,
        ];
    }

    /**
     * Get financial summary
     */
    private function getFinancialSummary($request)
    {
        
        if (!current_property()) {
            return [];
        }
        $query = Booking::where('property_id', current_property()->id)
            ->where('status', 'confirmed');
        
        if ($request->filled('date_from')) {
            $query->whereDate('arrival_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('departure_date', '<=', $request->date_to);
        }

        return [
            'total_revenue' => $query->sum('total_amount'),
            'total_bookings' => $query->count(),
            'avg_booking_value' => $query->avg('total_amount'),
            'avg_daily_rate' => $query->avg('daily_rate'),
        ];
    }

    /**
     * Get user activity summary
     */
    private function getUserActivitySummary($request)
    {
        $query = TenantUserActivity::query();
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return [
            'total_activities' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'top_actions' => $query->select('action', DB::raw('COUNT(*) as count'))
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('count', 'action'),
            'activities_today' => $query->whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Get revenue trends
     */
    private function getRevenueTrends($request)
    {
        
        if (!current_property()) {
            return [];
        }

        $dateFrom = $request->date_from ?? now()->subDays(30)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        return Booking::where('property_id', current_property()->id)
            ->where('status', 'confirmed')
            ->whereDate('arrival_date', '>=', $dateFrom)
            ->whereDate('arrival_date', '<=', $dateTo)
            ->selectRaw('DATE(arrival_date) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Generate occupancy report
     */
    protected function getOccupancyReport()
    {
        if (!current_property()) {
            return [];
        }
        $property = current_property();
        
        $data = [
            'monthly_occupancy' => Booking::where('property_id', $property->id)
                ->selectRaw('MONTH(arrival_date) as month, 
                            YEAR(arrival_date) as year,
                            COUNT(*) as total_bookings,
                            AVG(DATEDIFF(departure_date, arrival_date)) as avg_stay_length')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),

            'current_occupancy' => Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->where('arrival_date', '<=', now())
                ->where('departure_date', '>=', now())
                ->count(),

            'total_rooms' => Room::where('property_id', $property->id)->count()
        ];

        $data['occupancy_rate'] = $data['total_rooms'] > 0 
            ? round(($data['current_occupancy'] / $data['total_rooms']) * 100, 2) 
            : 0;

        return $data;
    }

    /**
     * Generate revenue report
     */
    protected function getRevenueReport()
    {
        if (!current_property()) {
            return [];
        }
        
        $property = current_property();
        
        return [
            'monthly_revenue' => Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->selectRaw('MONTH(arrival_date) as month, 
                            YEAR(arrival_date) as year,
                            SUM(total_amount) as revenue,
                            COUNT(*) as bookings')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),

            'ytd_revenue' => Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->whereYear('arrival_date', date('Y'))
                ->sum('total_amount'),

            'average_daily_rate' => Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->avg('daily_rate')
        ];
    }

    /**
     * Generate booking source report
     */
    protected function getBookingSourceReport()
    {
        $property = current_property();
        
        return Booking::where('property_id', $property->id)
            ->selectRaw('COALESCE(source, "Direct") as source, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Generate room performance report
     */
    protected function getRoomPerformanceReport()
    {
        $property = current_property();
        
        return Room::with(['type', 'bookings' => function($query) {
                $query->where('status', 'confirmed');
            }])
            ->where('property_id', $property->id)
            ->get()
            ->map(function($room) {
                return [
                    'room_number' => $room->number,
                    'room_type' => $room->type->name,
                    'total_bookings' => $room->bookings->count(),
                    'total_revenue' => $room->bookings->sum('total_amount'),
                    'occupancy_rate' => $room->bookings->count() > 0 
                        ? round(($room->bookings->count() / 30) * 100, 2) // Simple 30-day calculation
                        : 0
                ];
            })
            ->sortByDesc('total_revenue');
    }

    /**
     * Get occupancy data
     */
    private function getOccupancyData($dateFrom, $dateTo, $propertyId = null)
    {
        
        if (!current_property()) {
            return [];
        }
        $query = Booking::selectRaw('DATE(arrival_date) as date, COUNT(*) as bookings')
            ->whereDate('arrival_date', '>=', $dateFrom)
            ->whereDate('arrival_date', '<=', $dateTo)
            ->where('status', '!=', 'cancelled')
            ->where('property_id', current_property()->id);

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        return $query->groupBy('date')->orderBy('date')->get();
    }

    /**
     * Get room performance data
     */
    private function getRoomPerformanceData($dateFrom, $dateTo, $propertyId = null)
    {
        
        if (!current_property()) {
            return [];
        }
        $query = Booking::with('room')
            ->selectRaw('room_id, COUNT(*) as booking_count, AVG(DATEDIFF(departure_date, arrival_date)) as avg_nights')
            ->whereDate('arrival_date', '>=', $dateFrom)
            ->whereDate('departure_date', '<=', $dateTo)
            ->where('status', '!=', 'cancelled')
            ->where('property_id', current_property()->id);

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        return $query->groupBy('room_id')
            ->orderByDesc('booking_count')
            ->limit(10)
            ->get();
    }

    /**
     * Export reports to CSV/Excel
     */
    public function export(Request $request, $type)
    {
        
        if (!current_property()) {
            return response()->json(['error' => 'No property selected for export'], 400);
        }
        $request->validate([
            'report_type' => 'required|in:occupancy,revenue,bookings,rooms,financial,user_activity'
        ]);

        $this->logTenantActivity('report_export', 'Exported Report', null, [
            'report_type' => $request->report_type,
            'format' => $type
        ]);

        $property = current_property();
        $data = [];

        switch ($request->report_type) {
            case 'occupancy':
                $data = $this->getOccupancyReport();
                $filename = 'occupancy-report-' . date('Y-m-d') . '.csv';
                break;

            case 'revenue':
                $data = $this->getRevenueReport();
                $filename = 'revenue-report-' . date('Y-m-d') . '.csv';
                break;

            case 'bookings':
                $data = $this->getBookingSourceReport();
                $filename = 'bookings-report-' . date('Y-m-d') . '.csv';
                break;

            case 'rooms':
                $data = $this->getRoomPerformanceReport();
                $filename = 'rooms-report-' . date('Y-m-d') . '.csv';
                break;

            case 'financial':
                $data = $this->getFinancialSummary($request);
                $filename = 'financial-report-' . date('Y-m-d') . '.csv';
                break;

            case 'user_activity':
                $data = $this->getUserActivitySummary($request);
                $filename = 'user-activity-report-' . date('Y-m-d') . '.csv';
                break;
        }

        if ($type === 'csv') {
            return $this->exportToCsv($data, $filename);
        }

        return response()->json(['error' => 'Export type not supported'], 400);
    }

    /**
     * Simple CSV export
     */
    protected function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add header row based on data type
            if (isset($data['monthly_occupancy'])) {
                fputcsv($file, ['Month', 'Year', 'Bookings', 'Average Stay Length']);
                foreach ($data['monthly_occupancy'] as $row) {
                    fputcsv($file, [
                        $row->month,
                        $row->year,
                        $row->total_bookings,
                        $row->avg_stay_length
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
