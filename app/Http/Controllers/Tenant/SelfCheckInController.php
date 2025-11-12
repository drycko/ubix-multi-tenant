<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Booking;
use App\Models\Tenant\DigitalKey;
use App\Models\Tenant\Guest;
use App\Traits\LogsTenantUserActivity;
use App\Services\Tenant\NotificationService;
use Illuminate\Support\Str;

class SelfCheckInController extends Controller
{
    use LogsTenantUserActivity;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    protected function getGuest()
    {
        return session('guest_id') ? Guest::find(session('guest_id')) : null;
    }

    // Show self check-in page
    public function index(Request $request)
    {
        $guest = $this->getGuest();
        
        if (!$guest) {
            return redirect()->route('tenant.guest-portal.login');
        }

        // Get bookings eligible for check-in (confirmed and arrival date is today or past)
        $checkInBookings = Booking::whereHas('guests', function($q) use ($guest) {
                $q->where('guest_id', $guest->id);
            })
            ->where('status', 'confirmed')
            ->where('arrival_date', '<=', now()->format('Y-m-d'))
            ->where('departure_date', '>', now()->format('Y-m-d'))
            ->with(['room.type', 'property', 'guests'])
            ->get();

        // Get bookings eligible for check-out (checked_in)
        $checkOutBookings = Booking::whereHas('guests', function($q) use ($guest) {
                $q->where('guest_id', $guest->id);
            })
            ->where('status', 'checked_in')
            ->with(['room.type', 'property', 'guests', 'digitalKeys' => function($q) {
                $q->where('active', true);
            }])
            ->get();

        return view('tenant.guest-portal.checkin', compact('guest', 'checkInBookings', 'checkOutBookings'));
    }

    // Handle check-in
    public function checkIn(Request $request, $bookingId)
    {
        $guest = $this->getGuest();
        
        if (!$guest) {
            return redirect()->route('tenant.guest-portal.login');
        }

        $booking = Booking::whereHas('guests', function($q) use ($guest) {
                $q->where('guest_id', $guest->id);
            })
            ->with(['room', 'guests'])
            ->findOrFail($bookingId);

        // Validate booking status
        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'This booking is not eligible for check-in.');
        }

        // Validate arrival date
        if ($booking->arrival_date > now()->format('Y-m-d')) {
            return back()->with('error', 'Check-in date has not arrived yet.');
        }

        // Validate departure date
        if ($booking->departure_date <= now()->format('Y-m-d')) {
            return back()->with('error', 'This booking has expired.');
        }

        // Update booking status
        $booking->status = 'checked_in';
        $booking->save();

        // Generate digital key for each guest
        foreach ($booking->guests as $bookingGuest) {
            $keyCode = $this->generateKeyCode();
            
            DigitalKey::create([
                'booking_id' => $booking->id,
                'room_id' => $booking->room_id,
                'guest_id' => $bookingGuest->id,
                'key_code' => $keyCode,
                'issued_at' => now(),
                'expires_at' => $booking->departure_date . ' 23:59:59',
                'active' => true,
            ]);
        }

        // Log activity
        $this->logActivity(
            'guest_checked_in',
            "Guest {$guest->full_name} checked in for booking {$booking->bcode}",
            $booking
        );

        // Send notification
        $this->notificationService->sendNotification(
            'guest_checked_in',
            $booking,
            ['guest' => $guest]
        );

        return redirect()
            ->route('tenant.guest-portal.bookings.show', $booking->id)
            ->with('success', 'Check-in successful! Your digital key has been issued.');
    }

    // Handle check-out
    public function checkOut(Request $request, $bookingId)
    {
        $guest = $this->getGuest();
        
        if (!$guest) {
            return redirect()->route('tenant.guest-portal.login');
        }

        $booking = Booking::whereHas('guests', function($q) use ($guest) {
                $q->where('guest_id', $guest->id);
            })
            ->with(['digitalKeys'])
            ->findOrFail($bookingId);

        // Validate booking status
        if ($booking->status !== 'checked_in') {
            return back()->with('error', 'This booking is not eligible for check-out.');
        }

        // Update booking status
        $booking->status = 'checked_out';
        $booking->save();

        // Deactivate all digital keys for this booking
        foreach ($booking->digitalKeys as $key) {
            $key->active = false;
            $key->save();
        }

        // Log activity
        $this->logActivity(
            'guest_checked_out',
            "Guest {$guest->full_name} checked out from booking {$booking->bcode}",
            $booking
        );

        // Send notification
        $this->notificationService->sendNotification(
            'guest_checked_out',
            $booking,
            ['guest' => $guest]
        );

        return redirect()
            ->route('tenant.guest-portal.bookings.show', $booking->id)
            ->with('success', 'Check-out successful! Thank you for staying with us. Please leave a review!');
    }

    /**
     * Generate a unique digital key code
     */
    protected function generateKeyCode()
    {
        do {
            // Generate a 6-digit key code
            $keyCode = strtoupper(Str::random(4) . rand(10, 99));
        } while (DigitalKey::where('key_code', $keyCode)->where('active', true)->exists());

        return $keyCode;
    }
}
