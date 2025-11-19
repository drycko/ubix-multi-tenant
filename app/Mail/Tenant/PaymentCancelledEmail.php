<?php

namespace App\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentCancelledEmail extends Mailable
{
  use Queueable, SerializesModels;

  public $payment;
  public $invoice;
  public $guest;

  public function __construct($payment, $invoice, $guest)
  {
    $this->payment = $payment;
    $this->invoice = $invoice;
    $this->guest = $guest;
  }

  public function build()
  {
    return $this->subject('Booking Payment Cancelled')
      ->markdown('emails.tenant.payment-cancelled');
  }
}