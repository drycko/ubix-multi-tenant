<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\RoomChange;
use App\Models\Tenant\Room;
use App\Models\Tenant\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RoomChangeController extends Controller
{
    /**
     * Display a listing of room changes.
     */
    public function index(Request $request)
    {
        $query = RoomChange::with(['booking', 'originalRoom', 'newRoom', 'changedBy']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date if provided
        if ($request->has('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        $roomChanges = $query->latest()->paginate(10);

        return view('tenant.room-changes.index', compact('roomChanges'));
    }

    /**
     * Show the form for creating a new room change.
     */
    public function create()
    {
        $bookings = Booking::active()->get();
        $rooms = Room::available()->get();

        return view('tenant.room-changes.create', compact('bookings', 'rooms'));
    }

    /**
     * Store a newly created room change in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'original_room_id' => [
                'required',
                'exists:rooms,id',
                Rule::exists('bookings', 'room_id')->where('id', $request->booking_id)
            ],
            'new_room_id' => [
                'required',
                'exists:rooms,id',
                'different:original_room_id'
            ],
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'scheduled_date' => 'required|date|after_or_equal:today'
        ]);

        $roomChange = RoomChange::create([
            ...$validated,
            'changed_by' => Auth::id(),
            'status' => RoomChange::STATUSES['PENDING']
        ]);

        return redirect()
            ->route('tenant.room-changes.show', $roomChange)
            ->with('success', 'Room change has been scheduled successfully.');
    }

    /**
     * Display the specified room change.
     */
    public function show(RoomChange $roomChange)
    {
        $roomChange->load(['booking', 'originalRoom', 'newRoom', 'changedBy']);
        
        return view('tenant.room-changes.show', compact('roomChange'));
    }

    /**
     * Show the form for editing the specified room change.
     */
    public function edit(RoomChange $roomChange)
    {
        if (!$roomChange->isPending()) {
            return redirect()
                ->route('tenant.room-changes.show', $roomChange)
                ->with('error', 'Only pending room changes can be edited.');
        }

        $bookings = Booking::active()->get();
        $rooms = Room::available()->get();

        return view('tenant.room-changes.edit', compact('roomChange', 'bookings', 'rooms'));
    }

    /**
     * Update the specified room change in storage.
     */
    public function update(Request $request, RoomChange $roomChange)
    {
        if (!$roomChange->isPending()) {
            return redirect()
                ->route('tenant.room-changes.show', $roomChange)
                ->with('error', 'Only pending room changes can be updated.');
        }

        $validated = $request->validate([
            'new_room_id' => [
                'required',
                'exists:rooms,id',
                'different:original_room_id'
            ],
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'scheduled_date' => 'required|date|after_or_equal:today'
        ]);

        $roomChange->update($validated);

        return redirect()
            ->route('tenant.room-changes.show', $roomChange)
            ->with('success', 'Room change has been updated successfully.');
    }

    /**
     * Remove the specified room change from storage.
     */
    public function destroy(RoomChange $roomChange)
    {
        if (!$roomChange->isPending()) {
            return redirect()
                ->route('tenant.room-changes.show', $roomChange)
                ->with('error', 'Only pending room changes can be deleted.');
        }

        $roomChange->delete();

        return redirect()
            ->route('tenant.room-changes.index')
            ->with('success', 'Room change has been deleted successfully.');
    }

    /**
     * Mark the room change as completed.
     */
    public function complete(RoomChange $roomChange)
    {
        if (!$roomChange->isPending()) {
            return redirect()
                ->route('tenant.room-changes.show', $roomChange)
                ->with('error', 'Only pending room changes can be completed.');
        }

        // Update the booking's room
        $roomChange->booking->update(['room_id' => $roomChange->new_room_id]);
        
        // Mark the room change as completed
        $roomChange->complete();

        return redirect()
            ->route('tenant.room-changes.show', $roomChange)
            ->with('success', 'Room change has been completed successfully.');
    }

    /**
     * Mark the room change as cancelled.
     */
    public function cancel(RoomChange $roomChange)
    {
        if (!$roomChange->isPending()) {
            return redirect()
                ->route('tenant.room-changes.show', $roomChange)
                ->with('error', 'Only pending room changes can be cancelled.');
        }

        $roomChange->cancel();

        return redirect()
            ->route('tenant.room-changes.show', $roomChange)
            ->with('success', 'Room change has been cancelled successfully.');
    }
}
