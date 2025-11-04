<?php

namespace App\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewBookingNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct($booking)
    {
      $this->booking = $booking;
    }

    public function build()
    {
      return $this->subject('New Booking Received')
        ->markdown('emails.tenant.new-booking-notification')
        ->with([
          'booking' => $this->booking,
        ]);
    }
}