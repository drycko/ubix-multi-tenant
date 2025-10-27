<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant\BookingInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenantGuestInvoiceEmail extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public $bookingInvoice;
  public $emailBody;

  /**
   * Create a new message instance.
   */
  public function __construct(BookingInvoice $bookingInvoice)
  {
    $this->bookingInvoice = $bookingInvoice;
  }

  /**
   * Build the message.
   */
  public function build()
  {
    $property = current_property();
    $subject = "Invoice " . $this->bookingInvoice->invoice_number . " from " . $property->name;

    return $this->subject($subject)
        ->markdown('emails.tenant.guest-invoice')
        ->with([
            'bookingInvoice' => $this->bookingInvoice,
        ])
        ->attachData($this->bookingInvoice->generatePdf(), 'invoice_' . $this->bookingInvoice->invoice_number . '.pdf', [
            'mime' => 'application/pdf',
        ]);
  }
}