<?php

namespace App\Services\Tenant;

use App\Mail\Tenant\TenantGuestInvoiceEmail;
use App\Mail\Tenant\BookingInformtionEmail;
use App\Mail\Tenant\PaymentReceiptEmail;
use App\Mail\Tenant\NewBookingNotificationEmail;
use App\Models\Tenant\Booking;
use App\Models\Tenant\InvoicePayment;
use App\Models\Tenant\BookingInvoice;
use App\Models\Tenant\TenantSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
  public function sendBookingInformationToGuest(Booking $booking): void
  {
    // build the email context
    $context = [
      'booking_id' => $booking->id,
      'booking_code' => $booking->bcode,
      'guest_id' => $booking->guests->first()->id ?? null,
      'guest_email' => $booking->guests->first()->email ?? null,
    ];
    // if local environment, log email sending
    if (app()->environment('local')) {
      $this->logEmail('info', 'sending_booking_confirmation_email', $context);
    }
    // Implementation for sending booking confirmation raw email to guest through mailer Mail/BookingInformationEmail
    Mail::to($context['guest_email'])->send(new BookingInformationEmail($booking, $booking->primary_guest));

    // This is a placeholder for the actual email sending logic
    Log::info("Sending booking confirmation email to guest for Booking ID: " . $booking->id);
  }

  public function sendNewBookingNotificationToAdmin(Booking $booking): void
  {
    // build the email context
    $context = [
      'booking_id' => $booking->id,
      'booking_code' => $booking->bcode,
      'guest_id' => $booking->guests->first()->id ?? null,
      'guest_email' => $booking->guests->first()->email ?? null,
    ];
    // Implementation for sending new booking notification email to admin
    $admin_email = TenantSetting::getSetting('tenant_admin_email') ?? config('mail.admin_address');
    Mail::to($admin_email)->send(new NewBookingNotificationEmail($context));
    // This is a placeholder for the actual email sending logic
    Log::info("Sending new booking notification email to admin for Booking ID: " . $booking->id);
  }

  /**
   * Send payment receipt email to guest.
   */
  public function sendPaymentReceiptToGuest(InvoicePayment $payment): void
  {
    // log sending payment receipt email
    $this->logEmail('info', 'sending_payment_receipt_email', [
      'payment_id' => $payment->id,
      'booking_id' => $payment->booking->id,
      'guest_id' => $payment->booking->guests->first()->id ?? null,
      'guest_email' => $payment->booking->guests->first()->email ?? null,
    ]);
    // Implementation for sending payment receipt email to guest
    $guestEmail = $payment->booking->guests->first()->email ?? null;
    $invoice = $payment->invoice;
    $guest = $payment->booking->guests->first();
    if ($guestEmail) {
      Mail::to($guestEmail)->send(new PaymentReceiptEmail($payment, $invoice, $guest));
    }
    // This is a placeholder for the actual email sending logic
    Log::info("Sending payment receipt email to guest for Payment ID: " . $payment->id);
  }

  /**
   * Send payment failed email to guest.
   */
  public function sendPaymentFailedToGuest(InvoicePayment $payment, string $failureReason): void
  {
    // log sending payment failed email
    $this->logEmail('info', 'sending_payment_failed_email', [
      'payment_id' => $payment->id,
      'booking_id' => $payment->booking->id,
      'guest_id' => $payment->booking->guests->first()->id ?? null,
      'guest_email' => $payment->booking->guests->first()->email ?? null,
      'failure_reason' => $failureReason,
    ]);
    // Implementation for sending payment failed email to guest
    $guestEmail = $payment->booking->guests->first()->email ?? null;
    if ($guestEmail) {
      // You would create a PaymentFailedEmail Mailable class similar to PaymentReceiptEmail
      Mail::to($guestEmail)->send(new PaymentFailedEmail($payment, $failureReason));
    }
    // This is a placeholder for the actual email sending logic
    Log::info("Sending payment failed email to guest for Payment ID: " . $payment->id);
  }

  /**
   * Send payment cancelled email to guest.
   */
  public function sendPaymentCancelledToGuest(InvoicePayment $payment): void
  {
    // log sending payment cancelled email
    $this->logEmail('info', 'sending_payment_cancelled_email', [
      'payment_id' => $payment->id,
      'booking_id' => $payment->booking->id,
      'guest_id' => $payment->booking->guests->first()->id ?? null,
      'guest_email' => $payment->booking->guests->first()->email ?? null,
    ]);
    // Implementation for sending payment cancelled email to guest
    $guestEmail = $payment->booking->guests->first()->email ?? null;
    if ($guestEmail) {
      // You would create a PaymentCancelledEmail Mailable class similar to PaymentReceiptEmail
      Mail::to($guestEmail)->send(new PaymentCancelledEmail($payment));
    }
    // This is a placeholder for the actual email sending logic
    Log::info("Sending payment cancelled email to guest for Payment ID: " . $payment->id);
  }

  /**
   * Send invoice email to guest with options.
   */
  public function sendInvoiceEmail(BookingInvoice $bookingInvoice, array $options = []): void
  {
    $recipientEmail = $options['recipient_email'] ?? null;
    $ccEmails = $options['cc_emails'] ?? [];

    $context = [
      'invoice_id' => $bookingInvoice->id,
      'invoice_number' => $bookingInvoice->invoice_number,
      'booking_id' => $bookingInvoice->booking->id,
      'booking_code' => $bookingInvoice->booking->bcode,
      'url' => $bookingInvoice->getInvoiceUrl(),
    ];

    // log sending invoice email
    $this->logEmail('info', 'sending_invoice_email', array_merge($context, [
      'recipient_email' => $recipientEmail,
    ]));
    if (empty($recipientEmail)) {
      throw new \Exception("No recipient email found for Invoice ID: " . $bookingInvoice->id);
    }

    // if in send email mode, send the email
    if (config('mail.enabled')) {
      Log::info("Mail is enabled. Proceeding to send invoice email for Invoice ID: " . $bookingInvoice->id);
    } else {
      Log::warning("Mail is disabled in configuration. Skipping email sending for Invoice ID: " . $bookingInvoice->id);
      return;
    }
    try {
      $email = Mail::to($recipientEmail);
      if (!empty($ccEmails)) {
        $email->cc($ccEmails);
      }
      $email->send(new TenantGuestInvoiceEmail($bookingInvoice));

      Log::info("Invoice email sent successfully to guest for Invoice ID: " . $bookingInvoice->id);
    } catch (\Exception $e) {
      Log::error("Failed to send invoice email for Invoice ID: " . $bookingInvoice->id . ". Error: " . $e->getMessage());
    }
  }

  private function performSendInvoiceEmail(BookingInvoice $bookingInvoice): void
  {
    try {
      // Log only user info, NOT temp password in production
      $context = [
        'invoice_id' => $bookingInvoice->id,
        'invoice_number' => $bookingInvoice->invoice_number,
        'booking_id' => $bookingInvoice->booking->id,
        'booking_code' => $bookingInvoice->booking->bcode,
      ];
      
      $recipientEmail = $bookingInvoice->booking->guests->first()->email;
      // log sending invoice email
      $this->logEmail('info', 'sending_invoice_email', array_merge($context, [
        'recipient_email' => $recipientEmail,
      ]));
      if (empty($recipientEmail)) {
        throw new \Exception("No recipient email found for Invoice ID: " . $bookingInvoice->id);
      }

      // if in send email mode, send the email
      if (config('mail.enabled')) {
        Log::info("Mail is enabled. Proceeding to send invoice email for Invoice ID: " . $bookingInvoice->id);
      } else {
        Log::warning("Mail is disabled in configuration. Skipping email sending for Invoice ID: " . $bookingInvoice->id);
        return;
      }
      Mail::to($recipientEmail)->send(new TenantGuestInvoiceEmail($bookingInvoice));

      Log::info("Invoice email sent successfully to guest for Invoice ID: " . $bookingInvoice->id);
    } catch (\Exception $e) {
      Log::error("Failed to send invoice email for Invoice ID: " . $bookingInvoice->id . ". Error: " . $e->getMessage());
    }
  }

  /**
   * Log email-specific events to the dedicated email log channel.
   *
   * @param string $level  Log level: info, error, warning, etc.
   * @param string $event  Short event code (e.g. 'welcome_email_sent')
   * @param array  $context Additional structured context
   */
  public function logEmail(string $level, string $event, array $context = []): void
  {
    try {
      $payload = array_merge([
        'event' => $event,
        'timestamp' => now()->toDateTimeString(),
      ], $context);

      // Use the dedicated 'email' logging channel if available
      if (config('logging.channels.email')) {
        Log::channel('email')->log($level, $event, $payload);
      } else {
        // Fallback to default logging if channel missing
        Log::log($level, $event, $payload);
      }
    } catch (\Exception $e) {
      // As a last resort, write to the default log
      Log::error('Failed to write to email log channel', [
          'error' => $e->getMessage(),
      ]);
    }
  }
}