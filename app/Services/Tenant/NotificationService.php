<?php

namespace App\Services\Tenant;

use App\Mail\TenantGuestInvoiceEmail;
use App\Models\Tenant\BookingInvoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
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