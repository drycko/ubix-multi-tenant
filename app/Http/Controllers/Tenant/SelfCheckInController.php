<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Booking;
use App\Models\Tenant\DigitalKey;

class SelfCheckInController extends Controller
{
    // Show self check-in page
    public function index(Request $request)
    {
        // Show eligible bookings for check-in
        $bookings = Booking::where('guest_id', $request->user()?->id)
            ->where('status', 'pending')
            ->get();
        return view('tenant.guest-portal.checkin', compact('bookings'));
    }

    // Handle check-in
    public function checkIn(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        // ...check-in logic...
        $booking->status = 'checked_in';
        $booking->save();
        // Issue digital key
        // ...digital key logic...
        return redirect()->route('tenant.guest-portal.index')->with('success', 'Checked in successfully!');
    }

    // Handle check-out
    public function checkOut(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        // ...check-out logic...
        $booking->status = 'checked_out';
        $booking->save();
        // Deactivate digital key
        // ...digital key logic...
        return redirect()->route('tenant.guest-portal.index')->with('success', 'Checked out successfully!');
    }
}
