<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Booking;
use App\Models\Tenant\Room;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\Guest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_bookings' => Booking::count(),
            // Count of bookings with status 'confirmed' and arrival date today or in the future
            'current_bookings' => Booking::where('status', 'confirmed')->where('arrival_date', '>=', now())->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'total_rooms' => Room::count(),
            'available_rooms' => Room::where('is_enabled', true)->count(),
            'total_guests' => Guest::count(),
        ];

        // Get the currency of the current property

        $currency = current_property()->currency;
        // Fetch recent bookings with related room and primary guest information, ordered by latest bcode
        $recent_bookings = Booking::with(['room', 'bookingGuests.guest'])
            ->where('property_id', current_property()->id)
            ->latest('created_at')
            ->paginate(20);

        // return view('tenant.bookings.index', compact('bookings'));
        return view('tenant.dashboard', compact('stats', 'recent_bookings', 'currency'));
    }

    public function stats()
    {
        // Fetch and return stats data for the dashboard
        $stats = [
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::where('status', 'confirmed')->where('arrival_date', '>=', now())
            ->latest('arrival_date')->count(),
            'total_rooms' => Room::count(),
            'available_rooms' => Room::where('is_enabled', true)->count(),
            'total_guests' => Guest::count(),
        ];

        // Bookings Over Time (last 30 days, by created_at)
        $propertyId = current_property()->id;
        $bookingsCreated = Booking::where('bookings.property_id', $propertyId)
            ->whereDate('created_at', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labelsCreated = [];
        $dataCreated = [];
        $period = \Carbon\CarbonPeriod::create(now()->subDays(29), now());
        $bookingsByCreated = $bookingsCreated->keyBy('date');
        foreach ($period as $date) {
            $d = $date->format('Y-m-d');
            $labelsCreated[] = $d;
            $dataCreated[] = isset($bookingsByCreated[$d]) ? $bookingsByCreated[$d]->count : 0;
        }
        $bookingsOverTime = [
            'labels' => $labelsCreated,
            'data' => $dataCreated,
        ];

        // Bookings Over Time by arrival_date (last 30 days)
        $bookingsArrival = Booking::where('bookings.property_id', $propertyId)
            ->whereDate('arrival_date', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('DATE(arrival_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labelsArrival = [];
        $dataArrival = [];
        $bookingsByArrival = $bookingsArrival->keyBy('date');
        foreach ($period as $date) {
            $d = $date->format('Y-m-d');
            $labelsArrival[] = $d;
            $dataArrival[] = isset($bookingsByArrival[$d]) ? $bookingsByArrival[$d]->count : 0;
        }
        $bookingsByArrivalDate = [
            'labels' => $labelsArrival,
            'data' => $dataArrival,
        ];
        // Bookings by Room Type
        $roomTypeCounts = Booking::withoutGlobalScopes()
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->where('bookings.property_id', $propertyId)
            ->whereNull('bookings.deleted_at')
            ->selectRaw('rooms.room_type_id, COUNT(*) as count')
            ->groupBy('rooms.room_type_id')
            ->pluck('count', 'rooms.room_type_id');

        $roomTypeNames = RoomType::whereIn('id', $roomTypeCounts->keys())
            ->pluck('name', 'id');

        $bookingsByRoomType = [
            'labels' => $roomTypeNames->values()->all(),
            'data' => $roomTypeCounts->values()->all(),
        ];

        return view('tenant.stats', compact('stats', 'bookingsOverTime', 'bookingsByArrivalDate', 'bookingsByRoomType'));
    }
}
