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
    if (is_super_user() && !is_property_selected()) {
      // Global stats for super-user viewing all properties
      $stats = [
        'total_properties' => \App\Models\Tenant\Property::where('is_active', true)->count(),
        'total_bookings' => Booking::count(),
        'current_bookings' => Booking::where('status', 'confirmed')->where('arrival_date', '>=', now())->count(),
        'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
        'total_rooms' => Room::count(),
        'total_room_types' => RoomType::count(),
        'total_guests' => Guest::count(),
      ];
      
      // Get stats per property
      $properties = \App\Models\Tenant\Property::with(['rooms'])
      ->where('is_active', true)
      ->get()
      ->map(function ($property) {
        return [
          'id' => $property->id,
          'name' => $property->name,
          'rooms_count' => Room::where('property_id', $property->id)->count(),
          'bookings_count' => Booking::where('property_id', $property->id)->count(),
          'guests_count' => Guest::where('property_id', $property->id)->count(),
          'recent_bookings' => Booking::where('property_id', $property->id)
          ->latest()
          ->take(5) // Limit to 5 recent bookings
          ->get()
        ];
      });
      
      $currency = config('app.currency', 'USD');
      
      return view('tenant.dashboard-global', compact('stats', 'properties', 'currency'));
    } else {
      // Property-specific dashboard
      $propertyId = is_super_user() ? selected_property_id() : current_property()->id;
      
      $stats = [
        'total_bookings' => Booking::where('property_id', $propertyId)->count(),
        'current_bookings' => Booking::where('property_id', $propertyId)
        ->where('status', 'confirmed')
        ->where('arrival_date', '>=', now())
        ->count(),
        'cancelled_bookings' => Booking::where('property_id', $propertyId)
        ->where('status', 'cancelled')
        ->count(),
        'total_rooms' => Room::where('property_id', $propertyId)->count(),
        'available_rooms' => Room::where('property_id', $propertyId)
        ->where('is_enabled', true)
        ->count(),
        'total_guests' => Guest::where('property_id', $propertyId)->count(),
      ];
      
      $currency = current_property()->currency ?? config('app.currency', 'USD');
      
      // Fetch recent bookings for the selected property
      $recent_bookings = Booking::with(['room', 'bookingGuests.guest'])
      ->where('property_id', $propertyId)
      ->latest('created_at')
      ->paginate(20);
      
      return view('tenant.dashboard', compact('stats', 'recent_bookings', 'currency'));
    }
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
     $propertyId = selected_property_id();
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
  
  /**
  * Display the knowledge base
  */
  public function knowledgeBase()
  {
    $knowledgeBasePath = base_path('KNOWLEDGE_BASE.md');
    
    if (!file_exists($knowledgeBasePath)) {
      abort(404, 'Knowledge base not found');
    }
    
    $content = file_get_contents($knowledgeBasePath);
    
    return view('tenant.knowledge-base', compact('content'));
  }

  /**
   * Display a custom error page
   */
  public function errorPage()
  {
    return view('tenant.error');
  
  }

  /**
   * Display unauthorized access page
   */
  public function unauthorizedPage()
  {
    // redirect to dashboard with error message
    return redirect()->route('tenant.dashboard')->with('error', 'Unauthorized access. Please select a property to continue.');
    // return view('tenant.unauthorized');
  }
}
