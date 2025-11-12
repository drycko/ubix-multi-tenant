<?php

namespace App\Http\Controllers\Tenant\PaymentGateways;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BookingInvoice;
use App\Models\Tenant\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\Tenant\PaygateGatewayService;
use App\Services\GatewayCredentials\TenantCredentialFactory;
use App\Services\Tenant\NotificationService;
use App\Traits\LogsTenantUserActivity;

class PaygateGatewayController extends Controller
{
  use LogsTenantUserActivity;
  
  protected NotificationService $notificationService;
  
  public function __construct(NotificationService $notificationService)
  {
    $this->notificationService = $notificationService;
  }
  
  /**
  * Initiate a PayGate payment for a booking.
  *
  * Flow:
  *  - create a pending InvoicePayment
  *  - build the initiate payload via PaygateGatewayService
  *  - server-side POST to PayGate initiate endpoint
  *  - parse returned response for PAY_REQUEST_ID (preferred) or form inputs
  *  - compute CHECKSUM = md5(PAYGATE_ID + PAY_REQUEST_ID + REFERENCE + encryption_key)
  *  - render an auto-post view that posts PAY_REQUEST_ID and CHECKSUM to process.trans
  */
  public function initiatePayment(Request $request, $bookingInvoiceId)
  {
    try {
      $invoice = BookingInvoice::findOrFail($bookingInvoiceId);
      
      $booking = $invoice->booking;
      $amountDue = $invoice->remaining_balance;
      $bookingPrimaryGuest = $booking->primaryGuest;
      
      $loggedInUser = auth()->user() ?? null;
      
      DB::beginTransaction();
      // can we do first or create here so we do not duplicate payments?
      $payment = InvoicePayment::firstOrCreate([
        'property_id' => selected_property_id(),
        'booking_invoice_id' => $invoice->id,
        'amount' => $amountDue,
        'status' => 'pending',
      ], [
        'guest_id' => $bookingPrimaryGuest->id,
        'payment_method' => 'paygate',
        'payment_date' => now(),
        'reference_number' => 'PG-' . strtoupper(uniqid()),
        'notes' => 'Payment initiated via PayGate',
        'recorded_by' => $loggedInUser ? $loggedInUser->id : null,
        'meta' => ['initiated_by' => $loggedInUser ? $loggedInUser->id : null],
      ]);
      
      // resolve tenant id
      $tenantId = null;
      if (function_exists('current_tenant') && current_tenant()) {
        $tenantId = current_tenant()->id;
      } elseif ($loggedInUser && property_exists($loggedInUser, 'tenant_id')) {
        $tenantId = $loggedInUser->tenant_id;
      }
      if (empty($tenantId) && method_exists($booking, 'tenant')) {
        $tenant = $booking->tenant;
        $tenantId = $tenant->id ?? $tenantId;
      }
      
      // transient credential and service
      $credential = TenantCredentialFactory::makeForTenant($tenantId, 'paygate');
      $paygateService = new PaygateGatewayService($credential);
      
      $amountCents = intval(round((float)$amountDue * 100));
      $orderId = 'T' . ($tenantId ?? '0') . '-B' . $booking->id . '-P' . $payment->id . '-' . now()->timestamp;
      
      // build payload (includes CHECKSUM per your service)
      $payload = $paygateService->buildPayload([
        'order_id' => $orderId,
        'amount_cents' => $amountCents,
        'currency' => 'ZAR',
        'return_url' => route('tenant.paygate.return'),
        'notify_url' => route('tenant.paygate.notify'),
        'customer' => [
          'email' => $booking->guest_email ?? ($loggedInUser?->email ?? null),
          'name' => $booking->guest_name ?? ($loggedInUser?->name ?? null),
        ],
        'meta' => [
          'tenant_id' => $tenantId,
          'invoice_payment_id' => $payment->id,
        ],
      ]);
      
      // persist meta (we need to json encode $payment->meta first if payload is string)
      $orderRef = $payload['REFERENCE'] ?? $payload['order_id'] ?? $orderId;
      
      if (is_string($payment->meta)) {
        $payment->meta = json_decode($payment->meta, true);
      }
      $payment->meta = array_merge($payment->meta ?? [], [
        'order_id' => $orderRef,
        'payload_snapshot' => $payload,
        'tenant_id' => $tenantId,
      ]);
      $payment->save();
      
      DB::commit();
      
      // server-side initiate
      Log::info('paygate.endpoint', ['endpoint' => $paygateService->getEndpoint()]);
      $initEndpoint = $paygateService->getEndpoint('initiate');
      Log::info('paygate.initiate.post', ['endpoint' => $initEndpoint, 'order' => $orderRef]);
      
      $response = Http::asForm()->post($initEndpoint, $payload);
      
      if ($response->failed()) {
        Log::error('PayGate initiate request failed', [
          'status' => $response->status(),
          'body' => substr($response->body(), 0, 1000),
        ]);
        return redirect()->back()->with('error', 'Failed to contact PayGate.');
      }
      
      $body = $response->body();
      
      // 1) Attempt to parse HTML <form> and find PAY_REQUEST_ID and PAYGATE_ID there
      libxml_use_internal_errors(true);
      $dom = new \DOMDocument();
      $dom->loadHTML($body);
      $forms = $dom->getElementsByTagName('form');
      
      $payRequestId = null;
      $returnedPaygateId = null;
      $returnedReference = null;
      $processEndpoint = $paygateService->getEndpoint('redirect');
      
      if ($forms->length > 0) {
        $form = $forms->item(0);
        
        // extract inputs
        $inputs = $form->getElementsByTagName('input');
        foreach ($inputs as $input) {
          $name = $input->getAttribute('name');
          if ($name === '') {
            continue;
          }
          $value = $input->getAttribute('value');
          
          $lower = strtolower($name);
          if ($lower === 'pay_request_id' || strtoupper($name) === 'PAY_REQUEST_ID') {
            $payRequestId = $value;
          } elseif ($lower === 'paygate_id' || strtoupper($name) === 'PAYGATE_ID') {
            $returnedPaygateId = $value;
          } elseif ($lower === 'reference' || strtoupper($name) === 'REFERENCE') {
            $returnedReference = $value;
          }
        }
        
        // fallback heuristics: look for token-like inputs if exact names not present
        if (! $payRequestId) {
          foreach ($inputs as $input) {
            $name = $input->getAttribute('name');
            $value = $input->getAttribute('value');
            if ($name === '') continue;
            if (stripos($name, 'pay') !== false && stripos($name, 'request') !== false) {
              $payRequestId = $value;
              break;
            }
          }
        }
        
        // If the form action is present and different, normalize to absolute and prefer it
        $formAction = $form->getAttribute('action');
        if ($formAction) {
          $nextAction = $formAction;
          if (strpos($nextAction, 'http') !== 0) {
            $parsed = parse_url($initEndpoint);
            $scheme = $parsed['scheme'] ?? 'https';
            $host = $parsed['host'] ?? null;
            if ($host) {
              $nextAction = rtrim($scheme . '://' . $host, '/') . '/' . ltrim($nextAction, '/');
            }
          }
          $processEndpoint = $nextAction;
        }
      }
      
      // 2) If not found in form, try regex extraction from body (PAY_REQUEST_ID, PAYGATE_ID, REFERENCE)
      if (! $payRequestId) {
        if (preg_match('/name=["\']?PAY_REQUEST_ID["\']?\s+value=["\']([^"\']+)["\']/i', $body, $m)) {
          $payRequestId = $m[1];
        } elseif (preg_match('/PAY_REQUEST_ID[^:\=]*[:=\s]+"?([A-F0-9\-]{10,})"?/i', $body, $m)) {
          $payRequestId = $m[1];
        } elseif (preg_match('/"PAY_REQUEST_ID"\s*:\s*"([^"]+)"/i', $body, $m)) {
          $payRequestId = $m[1];
        }
      }
      if (! $returnedPaygateId) {
        if (preg_match('/name=["\']?PAYGATE_ID["\']?\s+value=["\']([^"\']+)["\']/i', $body, $m)) {
          $returnedPaygateId = $m[1];
        } elseif (preg_match('/"PAYGATE_ID"\s*:\s*"([^"]+)"/i', $body, $m)) {
          $returnedPaygateId = $m[1];
        }
      }
      if (! $returnedReference) {
        if (preg_match('/name=["\']?REFERENCE["\']?\s+value=["\']([^"\']+)["\']/i', $body, $m)) {
          $returnedReference = $m[1];
        } elseif (preg_match('/"REFERENCE"\s*:\s*"([^"]+)"/i', $body, $m)) {
          $returnedReference = $m[1];
        }
      }
      
      // 3) If we found PAY_REQUEST_ID, compute the redirect CHECKSUM per docs:
      // CHECKSUM = md5(PAYGATE_ID + PAY_REQUEST_ID + REFERENCE + encryption_key)
      if ($payRequestId) {
        // prefer values returned by PayGate if present (they are authoritative)
        $merchantIdToUse = $returnedPaygateId ?: $credential->merchant_id;
        $referenceToUse = $returnedReference ?: $orderRef;
        
        // encryption key: depending on your tenant setup this may be merchant_key or passphrase/signature_key
        // prefer merchant_key first, then passphrase/signature_key
        $encryptionKey = $credential->merchant_key ?? $credential->passphrase ?? $credential->signature_key ?? '';
        
        $checksumString = $merchantIdToUse . $payRequestId . $referenceToUse . $encryptionKey;
        $redirectChecksum = md5($checksumString);
        
        Log::info('paygate.redirect.prepare', [
          'merchant_used' => $merchantIdToUse,
          'PAY_REQUEST_ID' => $payRequestId,
          'REFERENCE_used' => $referenceToUse,
          'checksum' => $redirectChecksum,
          'process_endpoint' => $processEndpoint,
        ]);
        
        // Render redirect page that posts only PAY_REQUEST_ID and CHECKSUM (uppercase names per docs)
        return view('tenant.paygate.redirect', [
          'endpoint' => $processEndpoint,
          'payload' => [
            'PAY_REQUEST_ID' => $payRequestId,
            'CHECKSUM' => $redirectChecksum,
          ],
        ]);
      }
      
      // Nothing usable found in initiate response. Return raw body for inspection (safe dev fallback).
      Log::warning('PayGate initiate returned no usable PAY_REQUEST_ID; returning raw body for inspection', ['len' => strlen($body)]);
      return response($body, 200)->header('Content-Type', $response->header('Content-Type') ?? 'text/html');
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('PayGate payment initiation failed: ' . $e->getMessage(), [
        'booking_id' => $bookingInvoiceId,
        'exception' => $e,
      ]);
      return redirect()->back()->with('error', 'Failed to initiate PayGate payment: ' . $e->getMessage());
    }
  }
  
  
  /**
  * Notify endpoint (server-to-server).
  *
  * PayWeb will POST transaction results to this endpoint.
  * We must:
  *  - parse the incoming POST,
  *  - locate the InvoicePayment (prefer meta->pay_request_id, fallback to meta->order_id),
  *  - verify PAYGATE_ID matches tenant credential (optional sanity check),
  *  - recompute CHECKSUM per PayWeb docs (MD5 of concatenated field values (in incoming order) + encryption key),
  *  - update payment status/transaction_id/meta, send notifications, and reply with plain-text "OK".
  *
  * Important: If "OK" is not returned, PayWeb will retry the notify later.
  */
  public function notify(Request $request)
  {
    $payload = $request->all();
    Log::info('PayGate notify payload received', ['keys' => array_keys($payload)]);
    // we will need to check why we are getting 419 errors from paygate notify calls when we test
    
    try {
      DB::beginTransaction();
      
      // Prefer PAY_REQUEST_ID to find the payment; fallback to REFERENCE/order id
      $payRequestId = $payload['PAY_REQUEST_ID'] ?? $payload['pay_request_id'] ?? null;
      $reference    = $payload['REFERENCE'] ?? $payload['reference'] ?? null;
      
      $payment = null;
      if ($payRequestId) {
        $payment = InvoicePayment::where('meta->pay_request_id', $payRequestId)->first();
      }
      if (! $payment && $reference) {
        $payment = InvoicePayment::where('meta->order_id', $reference)->first();
      }
      
      if (! $payment) {
        Log::warning('PayGate notify could not find payment (by PAY_REQUEST_ID or REFERENCE)', [
          'PAY_REQUEST_ID' => $payRequestId,
          'REFERENCE' => $reference,
          'payload_keys' => array_keys($payload),
        ]);
        DB::rollBack();
        // Reply 404 so PayWeb sees it's missing (they will retry only if not OK)
        return response('Not found', 404)->header('Content-Type', 'text/plain');
      }
      
      $tenantId = $payment->meta['tenant_id'] ?? null;
      $credential = TenantCredentialFactory::makeForTenant($tenantId, 'paygate');
      $paygateService = new PaygateGatewayService($credential);
      
      // Optional: ensure PAYGATE_ID matches the credential for extra safety
      $incomingPaygateId = $payload['PAYGATE_ID'] ?? $payload['paygate_id'] ?? null;
      if ($incomingPaygateId && !empty($credential->merchant_id) && (string)$incomingPaygateId !== (string)$credential->merchant_id) {
        Log::warning('PayGate notify PAYGATE_ID mismatch', [
          'incoming' => $incomingPaygateId,
          'expected' => $credential->merchant_id ?? '(empty)',
          'order_ref' => $reference ?? $payment->meta['order_id'] ?? null,
        ]);
        DB::rollBack();
        return response('Invalid PAYGATE_ID', 400)->header('Content-Type', 'text/plain');
      }
      
      // Incoming checksum (case-insensitive)
      $incomingChecksum = $payload['CHECKSUM'] ?? $payload['checksum'] ?? null;
      if (empty($incomingChecksum)) {
        Log::warning('PayGate notify missing CHECKSUM', ['keys' => array_keys($payload)]);
        DB::rollBack();
        return response('Missing checksum', 400)->header('Content-Type', 'text/plain');
      }
      
      // Compute CHECKSUM per PayWeb docs:
      // "MD5 hash to verify payload integrity. Calculated from all fields + key."
      // We follow the pattern: remove CHECKSUM from payload, keep the incoming field order,
      // concatenate all values ('' . $v1 . $v2 . ...), append encryption key, md5().
      $payloadCopy = $payload;
      // remove any checksum keys present (case variants)
      foreach (['CHECKSUM','checksum'] as $k) {
        if (array_key_exists($k, $payloadCopy)) {
          unset($payloadCopy[$k]);
        }
      }
      
      // Preserve incoming order: array_values respects insertion order.
      $values = array_values($payloadCopy);
      $concatenated = implode('', array_map(function ($v) {
        // ensure scalar representation; convert arrays/objects to JSON for determinism
        if (is_array($v) || is_object($v)) {
          return json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        return (string)$v;
      }, $values));
      
      // Determine encryption key for checksum: prefer merchant_key, then passphrase/signature key
      $encryptionKey = $credential->merchant_key ?? $credential->passphrase ?? $credential->signature_key ?? '';
      // (for local testing you can fallback to 'secret' per PayWeb test docs in your environment)
      
      $computedChecksum = md5($concatenated . $encryptionKey);
      
      if (! hash_equals((string)$computedChecksum, (string)$incomingChecksum)) {
        // As an additional informative fallback, try recomputing using an alternative
        // approach used earlier in the integration (concatenate canonical fields).
        $fallbackComputed = $paygateService->verifyNotification($payload) ? 'service-verified' : 'service-mismatch';
        
        Log::warning('PayGate notify checksum mismatch', [
          'computed' => $computedChecksum,
          'incoming' => $incomingChecksum,
          'fallback' => $fallbackComputed,
          'order_ref' => $reference ?? $payment->meta['order_id'] ?? null,
        ]);
        
        DB::rollBack();
        return response('Invalid checksum', 400)->header('Content-Type', 'text/plain');
      }
      
      // Idempotency: if payment already processed as success, acknowledge and return OK
      if ($payment->status === 'success') {
        DB::commit();
        return response('OK', 200)->header('Content-Type', 'text/plain');
      }
      
      // Apply the notify payload and update payment status inside a transaction
      DB::transaction(function () use ($payment, $payload) {
        $gatewayStatus = strtolower($payload['TRANSACTION_STATUS'] ?? $payload['transaction_status'] ?? $payload['TRANSACTION_STATUS'] ?? '0');
        $resultCode = $payload['RESULT_CODE'] ?? $payload['result_code'] ?? null;
        
        // Save transaction id if provided
        $payment->transaction_id = $payload['TRANSACTION_ID'] ?? $payload['transaction_id'] ?? $payment->transaction_id;
        
        // Save notify payload for auditing
        $payment->meta = array_merge($payment->meta ?? [], [
          'notify_payload' => $payload,
        ]);
        
        // Interpret TRANSACTION_STATUS per PayWeb: 1 typically means success/completed
        $isSuccess = in_array((string)$gatewayStatus, ['1', 'success', 'paid', 'completed']);
        
        if ($isSuccess) {
          $payment->status = 'success';
          $payment->save();
          
          // update related booking/invoice
          if (method_exists($payment, 'invoice')) {
            $invoice = $payment->invoice;
            if ($invoice) {
              $invoice->update(['status' => 'paid']);
              $booking = $invoice->booking;
              if ($booking && $booking->status !== 'completed') {
                $booking->update(['status' => 'confirmed']);
                // send notifications
                $this->notificationService->sendBookingInformationToGuest($booking);
                $this->notificationService->sendPaymentReceiptToGuest($payment);
                $this->notificationService->sendNewBookingNotificationToAdmin($payment);
              }
            }
          }
        } else {
          $payment->status = 'failed';
          $payment->save();
        }
      });
      
      DB::commit();
      
      // Respond with plain-text OK as required by PayWeb
      return response('OK', 200)->header('Content-Type', 'text/plain');
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('PayGate notify processing failed: ' . $e->getMessage(), ['exception' => $e, 'payload' => array_slice($payload, 0, 50)]);
      // Return 500 so PayWeb may retry (they retry if not OK)
      return response('Error', 500)->header('Content-Type', 'text/plain');
    }
  }
  
  /**
  * Return/cancel endpoint for guest redirect.
  */
  public function return(Request $request)
  {
    $payload = $request->all();
    Log::info('PayGate return payload', ['keys' => array_keys($payload)]);
    
    $orderRef = $payload['REFERENCE'] ?? $payload['order_id'] ?? null;
    $payment = $orderRef ? InvoicePayment::where('meta->order_id', $orderRef)->first() : null;
    
    if (! $payment) {
      return redirect()->route('bookings.index')->withErrors('Payment not found. If you were charged, contact support.');
    }
    
    if ($payment->status === 'pending') {
      return view('payments.pending', compact('payment'));
    }
    
    if ($payment->status === 'success') {
      return view('payments.success', compact('payment'));
    }
    
    return view('payments.failed', compact('payment'));
  }
  
  /**
  * Notify endpoint (server-to-server).
  */
  // public function notify(Request $request)
  // {
  //   $payload = $request->all();
  //   Log::info('PayGate notify payload received', ['keys' => array_keys($payload)]);
  
  //   try {
  //     DB::beginTransaction();
  
  //     $orderRef = $payload['REFERENCE'] ?? $payload['order_id'] ?? $payload['merchant_ref'] ?? null;
  
  //     if (! $orderRef) {
  //       Log::warning('PayGate notify missing order id', $payload);
  //       DB::rollBack();
  //       return response('Missing order id', 400);
  //     }
  
  //     $payment = InvoicePayment::where('meta->order_id', $orderRef)->first();
  
  //     if (! $payment) {
  //       Log::warning('PayGate notify could not find payment for order', ['order' => $orderRef, 'payload_keys' => array_keys($payload)]);
  //       DB::rollBack();
  //       return response('Not found', 404);
  //     }
  
  //     $tenantId = $payment->meta['tenant_id'] ?? null;
  //     $credential = TenantCredentialFactory::makeForTenant($tenantId, 'paygate');
  //     $paygateService = new PaygateGatewayService($credential);
  
  //     if (! $paygateService->verifyNotification($payload)) {
  //       Log::warning('PayGate notify signature verification failed', ['order' => $orderRef]);
  //       DB::rollBack();
  //       return response('Invalid signature', 400);
  //     }
  
  //     if ($payment->status === 'success') {
  //       DB::commit();
  //       return response('OK', 200);
  //     }
  
  //     DB::transaction(function () use ($payment, $payload) {
  //       $gatewayStatus = strtolower($payload['status'] ?? $payload['PAYMENT_STATUS'] ?? 'unknown');
  
  //       $payment->transaction_id = $payload['TRANSACTION_ID'] ?? $payload['transaction_id'] ?? $payment->transaction_id;
  //       $payment->meta = array_merge($payment->meta ?? [], ['notify_payload' => $payload]);
  
  //       if (in_array($gatewayStatus, ['success', 'paid', 'completed'])) {
  //         $payment->status = 'success';
  //         $payment->save();
  
  //         if (method_exists($payment, 'invoice')) {
  //           $invoice = $payment->invoice;
  //           if ($invoice) {
  //             $invoice->update(['status' => 'paid']);
  //             $booking = $invoice->booking;
  //             if ($booking && $booking->status !== 'completed') {
  //               $booking->update(['status' => 'confirmed']);
  //               $this->notificationService->sendBookingConfirmationToGuest($booking);
  //               $this->notificationService->sendPaymentReceiptToGuest($payment);
  //               $this->notificationService->sendNewBookingNotificationToAdmin($payment);
  //             }
  //           }
  //         }
  //       } else {
  //         $payment->status = 'failed';
  //         $payment->save();
  //       }
  //     });
  
  //     DB::commit();
  //     return response('OK', 200);
  //   } catch (\Throwable $e) {
  //     DB::rollBack();
  //     Log::error('PayGate notify processing failed: ' . $e->getMessage(), $payload);
  //     return response('Error', 500);
  //   }
  // }
  
  // /**
  // * Return/cancel endpoint for guest redirect.
  // */
  // public function return(Request $request)
  // {
  //   $payload = $request->all();
  //   Log::info('PayGate return payload', ['keys' => array_keys($payload)]);
  
  //   $orderRef = $payload['REFERENCE'] ?? $payload['order_id'] ?? null;
  //   $payment = $orderRef ? InvoicePayment::where('meta->order_id', $orderRef)->first() : null;
  
  //   if (! $payment) {
  //     return redirect()->route('bookings.index')->withErrors('Payment not found. If you were charged, contact support.');
  //   }
  
  //   if ($payment->status === 'pending') {
  //     return view('payments.pending', compact('payment'));
  //   }
  
  //   if ($payment->status === 'success') {
  //     return view('payments.success', compact('payment'));
  //   }
  
  //   return view('payments.failed', compact('payment'));
  // }
}