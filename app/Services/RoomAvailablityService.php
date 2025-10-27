<?php

namespace App\Services;

use App\Models\Tenant\Room;
use App\Models\Tenant\RoomStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RoomAvailablityService
{
  /**
  * Check room availability for a given date range and property.
  *
  * @param int $propertyId The property ID to check availability for
  * @param Carbon $startDate The start date of the desired booking
  * @param Carbon $endDate The end date of the desired booking
  * @return array List of available rooms
  */
  public function getAvailableRooms(int $propertyId, Carbon $startDate, Carbon $endDate): array
  {
    // Fetch rooms that are not booked in the given date range( we also have tom check for bookings that overlap)
    // let us first get rooms that have the last in statuses(this is relationship table) = 'clean' or 'inspected'
    $availableRooms = Room::where('property_id', $propertyId)
    ->whereHas('status', function ($query) {
      $query->whereIn('status', [RoomStatus::STATUS_CLEAN, RoomStatus::STATUS_INSPECTED]);
    })
    ->whereDoesntHave('bookings', function ($query) use ($startDate, $endDate) {
      $query->where(function ($q) use ($startDate, $endDate) {
        $q->whereBetween('start_date', [$startDate, $endDate])
        ->orWhereBetween('end_date', [$startDate, $endDate])
        ->orWhere(function ($subQ) use ($startDate, $endDate) {
          $subQ->where('start_date', '<=', $startDate)
          ->where('end_date', '>=', $endDate);
        });
      });
    })
    ->get();
    
    return $availableRooms->toArray();
  }

  /**
   * Get all rooms for a given property with filters and their types and rates.
   * 
   * @param int $propertyId The property ID
   * @param array $filters Optional filters to apply (e.g., room type, status)
   * @return array List of rooms with their types and rates
   */
  public function getAllRooms(int $propertyId, array $filters = []): array
  {
    $rooms = Room::where('property_id', $propertyId)->with(['type', 'rates']);

    // Apply filters if any
    if (!empty($filters)) {
      $rooms->where(function ($query) use ($filters) {
        foreach ($filters as $field => $value) {
          $query->where($field, $value);
        }
      });
    }

    return $rooms->get()->toArray();
  }
}