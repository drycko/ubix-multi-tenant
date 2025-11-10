<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Booking;
use App\Models\Tenant\Room;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\Guest;
use App\Models\Tenant\Property;
use App\Models\Tenant\InvoicePayment;
use App\Models\Tenant\BookingInvoice;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    use LogsTenantUserActivity;

    /**
     * Display statistics and analytics
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Convert to Carbon instances
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Determine if user is super user and if property is selected
        $isSuperUser = is_super_user();
        $propertySelected = is_property_selected();
        $selectedPropertyId = selected_property_id();

        // Build query based on user role and property selection
        if ($isSuperUser && !$propertySelected) {
            // Super user without property selection - show all properties
            $bookingsQuery = Booking::query();
            $roomsQuery = Room::query();
            $guestsQuery = Guest::query();
            $paymentsQuery = InvoicePayment::query();
            $invoicesQuery = BookingInvoice::query();
            $properties = Property::all();
            $viewingAllProperties = true;
        } else {
            // Regular user or super user with property selected - filter by property
            $propertyId = $selectedPropertyId;
            $bookingsQuery = Booking::where('property_id', $propertyId);
            $roomsQuery = Room::where('property_id', $propertyId);
            $guestsQuery = Guest::where('property_id', $propertyId);
            $paymentsQuery = InvoicePayment::whereHas('bookingInvoice.booking', function($q) use ($propertyId) {
                $q->where('property_id', $propertyId);
            });
            $invoicesQuery = BookingInvoice::whereHas('booking', function($q) use ($propertyId) {
                $q->where('property_id', $propertyId);
            });
            $properties = Property::where('id', $propertyId)->get();
            $viewingAllProperties = false;
        }

        // Basic statistics
        $stats = [
            'total_bookings' => (clone $bookingsQuery)->count(),
            'active_bookings' => (clone $bookingsQuery)->whereIn('status', ['confirmed', 'checked_in'])->count(),
            'total_rooms' => (clone $roomsQuery)->count(),
            'available_rooms' => (clone $roomsQuery)->where('is_enabled', true)
                ->whereDoesntHave('bookings', function($q) {
                    $q->whereIn('status', ['confirmed', 'checked_in'])
                      ->where('arrival_date', '<=', now())
                      ->where('departure_date', '>=', now());
                })->count(),
            'total_guests' => (clone $guestsQuery)->count(),
            'total_revenue' => (clone $paymentsQuery)->where('status', 'completed')->sum('amount'),
            'pending_revenue' => (clone $invoicesQuery)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->get()
                ->sum(function($invoice) {
                    return $invoice->remaining_balance;
                }),
            'cancelled_bookings' => (clone $bookingsQuery)->where('status', 'cancelled')->count(),
        ];

        // Bookings over time (by created_at)
        $bookingsOverTime = $this->getBookingsOverTime($bookingsQuery, $startDate, $endDate);

        // Bookings by arrival date
        $bookingsByArrivalDate = $this->getBookingsByArrivalDate($bookingsQuery, $startDate, $endDate);

        // Bookings by room type
        $bookingsByRoomType = $this->getBookingsByRoomType($bookingsQuery, $startDate, $endDate);

        // Revenue over time
        $revenueOverTime = $this->getRevenueOverTime($paymentsQuery, $startDate, $endDate);

        // Guest sources (if you have a source field)
        $guestSources = $this->getGuestSources($bookingsQuery, $startDate, $endDate);

        // Occupancy rate calculation
        $occupancyRate = $stats['total_rooms'] > 0 
            ? round(($stats['active_bookings'] / $stats['total_rooms']) * 100, 1) 
            : 0;

        // Average booking duration
        $avgBookingDuration = (clone $bookingsQuery)
            ->whereNotNull('nights')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->avg('nights') ?? 0;

        // Top performing room types by revenue
        $topRoomTypes = $this->getTopRoomTypes($bookingsQuery, $startDate, $endDate);

        // Monthly comparison (current vs previous period)
        $monthlyComparison = $this->getMonthlyComparison($bookingsQuery, $paymentsQuery);

        // Property-specific stats if viewing single property
        $propertyStats = null;
        if (!$viewingAllProperties && $properties->count() > 0) {
            $property = $properties->first();
            $propertyStats = [
                'name' => $property->name,
                'total_rooms' => $property->rooms()->count(),
                'available_rooms' => $property->rooms()->where('status', 'available')->count(),
                'maintenance_rooms' => $property->rooms()->where('status', 'maintenance')->count(),
                'occupied_rooms' => $property->rooms()->where('status', 'occupied')->count(),
            ];
        }

        // Log activity
        $this->logTenantUserActivity(
            'viewed',
            'Statistics',
            null,
            'Viewed statistics and analytics' . ($viewingAllProperties ? ' (All Properties)' : ' for property: ' . $properties->first()->name),
            [
                'date_range' => ['start' => $startDate->format('Y-m-d'), 'end' => $endDate->format('Y-m-d')],
                'property_view' => $viewingAllProperties ? 'all' : 'single',
            ]
        );

        return view('tenant.stats.index', compact(
            'stats',
            'bookingsOverTime',
            'bookingsByArrivalDate',
            'bookingsByRoomType',
            'revenueOverTime',
            'guestSources',
            'occupancyRate',
            'avgBookingDuration',
            'topRoomTypes',
            'monthlyComparison',
            'startDate',
            'endDate',
            'properties',
            'viewingAllProperties',
            'propertyStats'
        ));
    }

    /**
     * Show print view for statistics
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function print(Request $request)
    {
        // Get the same data as index but for print view
        $data = $this->getStatsData($request);

        // Log activity
        $this->logTenantUserActivity(
            'printed',
            'Statistics',
            null,
            'Printed statistics report',
            ['date_range' => ['start' => $data['startDate']->format('Y-m-d'), 'end' => $data['endDate']->format('Y-m-d')]]
        );

        return view('tenant.stats.print', $data);
    }

    /**
     * Download statistics report as PDF
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request)
    {
        $format = $request->input('format', 'pdf'); // pdf or csv

        if ($format === 'csv') {
            return $this->downloadCsv($request);
        }

        // Get the same data as index
        $data = $this->getStatsData($request);

        // Log activity
        $this->logTenantUserActivity(
            'downloaded',
            'Statistics',
            null,
            'Downloaded statistics report as ' . strtoupper($format),
            ['date_range' => ['start' => $data['startDate']->format('Y-m-d'), 'end' => $data['endDate']->format('Y-m-d')]]
        );

        // Generate PDF using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tenant.stats.print', $data);
        
        $filename = 'statistics-report-' . $data['startDate']->format('Y-m-d') . '-to-' . $data['endDate']->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Download statistics report as CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private function downloadCsv(Request $request)
    {
        $data = $this->getStatsData($request);

        $filename = 'statistics-report-' . $data['startDate']->format('Y-m-d') . '-to-' . $data['endDate']->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['Statistics Report']);
            fputcsv($file, ['Date Range:', $data['startDate']->format('Y-m-d') . ' to ' . $data['endDate']->format('Y-m-d')]);
            fputcsv($file, ['Generated:', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Overall Statistics
            fputcsv($file, ['Overall Statistics']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Bookings', $data['stats']['total_bookings']]);
            fputcsv($file, ['Active Bookings', $data['stats']['active_bookings']]);
            fputcsv($file, ['Cancelled Bookings', $data['stats']['cancelled_bookings']]);
            fputcsv($file, ['Total Rooms', $data['stats']['total_rooms']]);
            fputcsv($file, ['Available Rooms', $data['stats']['available_rooms']]);
            fputcsv($file, ['Total Guests', $data['stats']['total_guests']]);
            fputcsv($file, ['Total Revenue', number_format($data['stats']['total_revenue'], 2)]);
            fputcsv($file, ['Pending Revenue', number_format($data['stats']['pending_revenue'], 2)]);
            fputcsv($file, ['Occupancy Rate', $data['occupancyRate'] . '%']);
            fputcsv($file, ['Average Booking Duration', round($data['avgBookingDuration'], 1) . ' nights']);
            fputcsv($file, []);

            // Bookings Over Time
            fputcsv($file, ['Bookings Created Over Time']);
            fputcsv($file, ['Date', 'Count']);
            foreach ($data['bookingsOverTime']['labels'] as $index => $label) {
                fputcsv($file, [$label, $data['bookingsOverTime']['data'][$index]]);
            }
            fputcsv($file, []);

            // Bookings by Arrival Date
            fputcsv($file, ['Bookings by Arrival Date']);
            fputcsv($file, ['Date', 'Count']);
            foreach ($data['bookingsByArrivalDate']['labels'] as $index => $label) {
                fputcsv($file, [$label, $data['bookingsByArrivalDate']['data'][$index]]);
            }
            fputcsv($file, []);

            // Bookings by Room Type
            fputcsv($file, ['Bookings by Room Type']);
            fputcsv($file, ['Room Type', 'Count']);
            foreach ($data['bookingsByRoomType']['labels'] as $index => $label) {
                fputcsv($file, [$label, $data['bookingsByRoomType']['data'][$index]]);
            }
            fputcsv($file, []);

            // Revenue Over Time
            fputcsv($file, ['Revenue Over Time']);
            fputcsv($file, ['Date', 'Amount']);
            foreach ($data['revenueOverTime']['labels'] as $index => $label) {
                fputcsv($file, [$label, number_format($data['revenueOverTime']['data'][$index], 2)]);
            }
            fputcsv($file, []);

            // Top Room Types
            if (count($data['topRoomTypes']) > 0) {
                fputcsv($file, ['Top Performing Room Types']);
                fputcsv($file, ['Room Type', 'Bookings', 'Revenue']);
                foreach ($data['topRoomTypes'] as $roomType) {
                    fputcsv($file, [$roomType['name'], $roomType['bookings'], number_format($roomType['revenue'], 2)]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get statistics data for print/download
     *
     * @param Request $request
     * @return array
     */
    private function getStatsData(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Convert to Carbon instances
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Determine if user is super user and if property is selected
        $isSuperUser = is_super_user();
        $propertySelected = is_property_selected();
        $selectedPropertyId = selected_property_id();

        // Build query based on user role and property selection
        if ($isSuperUser && !$propertySelected) {
            $bookingsQuery = Booking::query();
            $roomsQuery = Room::query();
            $guestsQuery = Guest::query();
            $paymentsQuery = InvoicePayment::query();
            $invoicesQuery = BookingInvoice::query();
            $properties = Property::all();
            $viewingAllProperties = true;
        } else {
            $propertyId = $selectedPropertyId;
            $bookingsQuery = Booking::where('booking.property_id', $propertyId);
            $roomsQuery = Room::where('property_id', $propertyId);
            $guestsQuery = Guest::where('property_id', $propertyId);
            $paymentsQuery = InvoicePayment::whereHas('bookingInvoice.booking', function($q) use ($propertyId) {
                $q->where('property_id', $propertyId);
            });
            $invoicesQuery = BookingInvoice::whereHas('booking', function($q) use ($propertyId) {
                $q->where('property_id', $propertyId);
            });
            $properties = Property::where('id', $propertyId)->get();
            $viewingAllProperties = false;
        }

        $stats = [
            'total_bookings' => (clone $bookingsQuery)->count(),
            'active_bookings' => (clone $bookingsQuery)->whereIn('status', ['confirmed', 'checked_in'])->count(),
            'total_rooms' => (clone $roomsQuery)->count(),
            'available_rooms' => (clone $roomsQuery)->where('is_enabled', true)
                ->whereDoesntHave('bookings', function($q) {
                    $q->whereIn('status', ['confirmed', 'checked_in'])
                      ->where('arrival_date', '<=', now())
                      ->where('departure_date', '>=', now());
                })->count(),
            'total_guests' => (clone $guestsQuery)->count(),
            'total_revenue' => (clone $paymentsQuery)->where('status', 'completed')->sum('amount'),
            'pending_revenue' => (clone $invoicesQuery)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->get()
                ->sum(function($invoice) {
                    return $invoice->remaining_balance;
                }),
            'cancelled_bookings' => (clone $bookingsQuery)->where('status', 'cancelled')->count(),
        ];

        $bookingsOverTime = $this->getBookingsOverTime($bookingsQuery, $startDate, $endDate);
        $bookingsByArrivalDate = $this->getBookingsByArrivalDate($bookingsQuery, $startDate, $endDate);
        $bookingsByRoomType = $this->getBookingsByRoomType($bookingsQuery, $startDate, $endDate);
        $revenueOverTime = $this->getRevenueOverTime($paymentsQuery, $startDate, $endDate);
        $guestSources = $this->getGuestSources($bookingsQuery, $startDate, $endDate);

        $occupancyRate = $stats['total_rooms'] > 0 
            ? round(($stats['active_bookings'] / $stats['total_rooms']) * 100, 1) 
            : 0;

        $avgBookingDuration = (clone $bookingsQuery)
            ->whereNotNull('nights')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->avg('nights') ?? 0;

        $topRoomTypes = $this->getTopRoomTypes($bookingsQuery, $startDate, $endDate);
        $monthlyComparison = $this->getMonthlyComparison($bookingsQuery, $paymentsQuery);

        $propertyStats = null;
        if (!$viewingAllProperties && $properties->count() > 0) {
            $property = $properties->first();
            $totalRooms = $property->rooms()->count();
            
            // Get rooms currently occupied (with active bookings)
            $occupiedRooms = $property->rooms()
                ->whereHas('bookings', function($q) {
                    $q->whereIn('status', ['confirmed', 'checked_in'])
                      ->where('arrival_date', '<=', now())
                      ->where('departure_date', '>=', now());
                })
                ->count();
            
            // Get rooms under maintenance (from room statuses)
            $maintenanceRooms = $property->rooms()
                ->whereHas('currentStatus', function($q) {
                    $q->whereIn('status', ['maintenance', 'out_of_order']);
                })
                ->count();
            
            // Calculate available rooms
            $availableRooms = $property->rooms()
                ->where('is_enabled', true)
                ->whereDoesntHave('bookings', function($q) {
                    $q->whereIn('status', ['confirmed', 'checked_in'])
                      ->where('arrival_date', '<=', now())
                      ->where('departure_date', '>=', now());
                })
                ->whereDoesntHave('currentStatus', function($q) {
                    $q->whereIn('status', ['maintenance', 'out_of_order']);
                })
                ->count();
            
            $propertyStats = [
                'name' => $property->name,
                'total_rooms' => $totalRooms,
                'available_rooms' => $availableRooms,
                'maintenance_rooms' => $maintenanceRooms,
                'occupied_rooms' => $occupiedRooms,
            ];
        }

        return compact(
            'stats',
            'bookingsOverTime',
            'bookingsByArrivalDate',
            'bookingsByRoomType',
            'revenueOverTime',
            'guestSources',
            'occupancyRate',
            'avgBookingDuration',
            'topRoomTypes',
            'monthlyComparison',
            'startDate',
            'endDate',
            'properties',
            'viewingAllProperties',
            'propertyStats'
        );
    }

    /**
     * Get bookings over time by created_at date
     */
    private function getBookingsOverTime($bookingsQuery, $startDate, $endDate)
    {
        $bookings = (clone $bookingsQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        // Fill in all dates in the range
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $dateStr;
            
            $booking = $bookings->firstWhere('date', $dateStr);
            $data[] = $booking ? $booking->count : 0;
            
            $currentDate->addDay();
        }

        return compact('labels', 'data');
    }

    /**
     * Get bookings by arrival date
     */
    private function getBookingsByArrivalDate($bookingsQuery, $startDate, $endDate)
    {
        $bookings = (clone $bookingsQuery)
            ->whereBetween('arrival_date', [$startDate, $endDate])
            ->selectRaw('DATE(arrival_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $dateStr;
            
            $booking = $bookings->firstWhere('date', $dateStr);
            $data[] = $booking ? $booking->count : 0;
            
            $currentDate->addDay();
        }

        return compact('labels', 'data');
    }

    /**
     * Get bookings by room type
     */
    private function getBookingsByRoomType($bookingsQuery, $startDate, $endDate)
    {
        $bookings = (clone $bookingsQuery)
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->selectRaw('room_types.name as room_type, COUNT(*) as count')
            ->groupBy('room_types.id', 'room_types.name')
            ->orderByDesc('count')
            ->get();

        $labels = $bookings->pluck('room_type')->toArray();
        $data = $bookings->pluck('count')->toArray();

        return compact('labels', 'data');
    }

    /**
     * Get revenue over time
     */
    private function getRevenueOverTime($paymentsQuery, $startDate, $endDate)
    {
        $payments = (clone $paymentsQuery)
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $dateStr;
            
            $payment = $payments->firstWhere('date', $dateStr);
            $data[] = $payment ? (float)$payment->total : 0;
            
            $currentDate->addDay();
        }

        return compact('labels', 'data');
    }

    /**
     * Get guest sources (if available)
     */
    private function getGuestSources($bookingsQuery, $startDate, $endDate)
    {
        // If you have a source column on bookings
        $sources = (clone $bookingsQuery)
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->whereNotNull('bookings.source')
            ->selectRaw('bookings.source, COUNT(*) as count')
            ->groupBy('bookings.source')
            ->orderByDesc('count')
            ->get();

        $labels = $sources->pluck('source')->toArray();
        $data = $sources->pluck('count')->toArray();

        return compact('labels', 'data');
    }

    /**
     * Get top performing room types by revenue
     */
    private function getTopRoomTypes($bookingsQuery, $startDate, $endDate)
    {
        return (clone $bookingsQuery)
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->selectRaw('room_types.name, COUNT(*) as bookings, SUM(bookings.total_amount) as revenue')
            ->groupBy('room_types.id', 'room_types.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'bookings' => $item->bookings,
                    'revenue' => (float)$item->revenue,
                ];
            })
            ->toArray();
    }

    /**
     * Get monthly comparison
     */
    private function getMonthlyComparison($bookingsQuery, $paymentsQuery)
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $currentMonthBookings = (clone $bookingsQuery)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->count();

        $previousMonthBookings = (clone $bookingsQuery)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $currentMonthRevenue = (clone $paymentsQuery)
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');

        $previousMonthRevenue = (clone $paymentsQuery)
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$previousMonthStart, $previousMonthEnd])
            ->sum('amount');

        $bookingsChange = $previousMonthBookings > 0 
            ? round((($currentMonthBookings - $previousMonthBookings) / $previousMonthBookings) * 100, 1)
            : 0;

        $revenueChange = $previousMonthRevenue > 0
            ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
            : 0;

        return [
            'current_month_bookings' => $currentMonthBookings,
            'previous_month_bookings' => $previousMonthBookings,
            'bookings_change' => $bookingsChange,
            'current_month_revenue' => (float)$currentMonthRevenue,
            'previous_month_revenue' => (float)$previousMonthRevenue,
            'revenue_change' => $revenueChange,
        ];
    }
}
