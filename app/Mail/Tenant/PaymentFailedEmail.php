<?php

namespace App\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentFailedEmail extends Mailable
{
  use Queueable, SerializesModels;

  public $payment;
  public $failureReason;

  public function __construct($payment, $failureReason)
  {
    $this->payment = $payment;
    $this->invoice = $payment->invoice;
    $this->guest = $payment->booking->guests->first();
    $this->failureReason = $failureReason;
  }

  public function build()
  {
    return $this->subject('Booking Payment Failed')
      ->markdown('emails.tenant.payment-failed');
  }
}
