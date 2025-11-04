<?php

namespace App\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class BookingInformationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $primaryGuest;

    public function __construct($booking, $primaryGuest)
    {
      $this->booking = $booking;
      $this->primaryGuest = $primaryGuest;
    }

    public function build()
    {
      return $this->subject('Booking Information')
        ->markdown('emails.tenant.booking-information');
    }
}