<?php

namespace App\Http\Controllers\Tenant\PaymentGateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Http;
use App\Models\Tenant\BookingInvoice;
use App\Http\Controllers\Controller;

class PayfastGatewayController extends Controller
{
  // Initiate PayFast payment
  // public function initiate(Request $request)
  // {
  //   try {
  //     // validate request and get payment details (do these need to be in Model? Probably)
  //     $request->validate([
  //         'invoice_id' => 'required|integer|exists:booking_invoices,id',
  //         // Add other necessary validations
  //     ]);
  //     // validate invoice existence
  //     $invoiceId = $request->input('invoice_id');
  //     $bookingInvoice = \App\Models\Tenant\BookingInvoice::find($invoiceId);
  //     if (!$bookingInvoice) {
  //       \Log::warning('Attempt to pay a non-existent invoice. Invoice ID: ' . $invoiceId);
  //       return redirect()->back()->withErrors(['Invoice not found.']);
  //     }
  //     // invoice should have a remaining balance
  //     if ($bookingInvoice->remaining_balance <= 0) {
  //       \Log::warning('Attempt to pay an invoice with no remaining balance. Invoice ID: ' . $bookingInvoice->id);
  //       return redirect()->back()->withErrors(['Invoice has no remaining balance.']);
  //     }
  //     // Set payment details
  //     $paymentDetails = [
  //         'amount' => $bookingInvoice->remaining_balance,
  //         'item_name' => 'Booking Invoice #' . $bookingInvoice->invoice_number,
  //         'name_first' => $bookingInvoice->booking->bookingGuests->first()->first_name,
  //         'name_last' => $bookingInvoice->booking->bookingGuests->first()->last_name,
  //         'email_address' => $bookingInvoice->booking->bookingGuests->first()->email,
  //         'cell_number' => $bookingInvoice->booking->bookingGuests->first()->phone_number,
  //         'm_payment_id' => $bookingInvoice->id,
  //     ];
  //     // TODO: Validate request and get payment details
  //     $tenantSettings = \App\Models\Tenant\TenantSetting::getSettings([
  //         'payfast_merchant_id',
  //         'payfast_merchant_key',
  //         'payfast_passphrase',
  //         'payfast_is_test',
  //     ]);

  //     $merchant_id = $tenantSettings['payfast_merchant_id'] ?? '';
  //     $merchant_key = \App\Models\Tenant\TenantSetting::getEncryptedSetting('payfast_merchant_key');
  //     $passphrase = \App\Models\Tenant\TenantSetting::getEncryptedSetting('payfast_passphrase');
  //     $testingMode = $tenantSettings['payfast_is_test'] ?? true;

  //     // Construct variables
  //     $cartTotal = $paymentDetails['amount']; // This amount needs to be sourced from your application;
  //     $data = array(
  //         // Merchant details
  //         'merchant_id' => $merchant_id,
  //         'merchant_key' => $merchant_key,
  //         'return_url' => route('tenant.payfast.return'),
  //         'cancel_url' => route('tenant.payfast.cancel'),
  //         'notify_url' => route('tenant.payfast.notify'),
  //         // Buyer details
  //         'name_first' => $paymentDetails['name_first'],
  //         'name_last'  => $paymentDetails['name_last'],
  //         'email_address'=> $paymentDetails['email_address'],
  //         'cell_number'=> $paymentDetails['cell_number'],
  //         // Transaction details
  //         'm_payment_id' => 'UBX-' . $paymentDetails['m_payment_id'],
  //         'amount' => number_format( sprintf( '%.2f', $cartTotal ), 2, '.', '' ),
  //         'item_name' => $paymentDetails['item_name'],
  //     );

  //     $signature = $this->generateSignature($data, $passphrase);
  //     $data['signature'] = $signature;

  //     // If in testing mode make use of either sandbox.payfast.co.za or www.payfast.co.za
  //     $pfHost = $testingMode ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
  //     $payfastUrl = 'https://' . $pfHost . '/eng/process';
      
  //     Log::info('PayFast payment data', $data);
      
  //     // Return a view with an auto-submitting form
  //     return view('tenant.payfast.redirect', [
  //       'action' => $payfastUrl,
  //       'data' => $data
  //     ]);
      
  //   } catch (\Exception $e) {
  //     Log::error('Error initiating PayFast payment: ' . $e->getMessage());
  //     return redirect()->back()->withErrors(['Error initiating payment.']);
  //   }
  // }

  // public function buildPayfastForm($data) {
  //   $formData = '<form action="https://'.$pfHost.'/eng/process" method="post">';
  //   foreach ($data as $key => $value) {
  //       $formData .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '"/>';
  //   }
  //   $formData .= '<button type="submit" class="btn btn-danger">
  //                 Pay with PayFast
  //                 </button>';
  //   return $formData;
  // }

  /**
   * Handle PayFast return (redirect after payment)
   * NOTE: PayFast does NOT reliably send payment data to return URL.
   * Payment confirmation is handled by ITN (notify endpoint).
   * This just shows a thank you page.
   */
  public function handleReturn(Request $request)
  {
    Log::info('PayFast return received', [
      'get' => $request->query(),
      'post' => $request->post(),
    ]);
    
    // Show a generic thank you page
    // Payment has already been processed via ITN
    return view('tenant.payfast.thank-you');
  }

  /**
   * Handle PayFast cancel
   */
  public function handleCancel(Request $request)
  {
    Log::info('PayFast cancel received', $request->all());
    
    // Extract m_payment_id (remove UBX- prefix if present)
    $mPaymentId = $request->input('m_payment_id');
    if (str_starts_with($mPaymentId, 'UBX-')) {
      $mPaymentId = substr($mPaymentId, 4);
    }
    
    $bookingInvoice = BookingInvoice::find($mPaymentId);
    
    if (!$bookingInvoice) {
      Log::warning('PayFast cancel: Invoice not found', ['m_payment_id' => $mPaymentId]);
      return redirect()->route('tenant.guest-portal.index')->with('error', 'Invoice not found.');
    }
    
    return view('tenant.payfast.cancel', compact('bookingInvoice'));
  }

  /**
   * Handle PayFast ITN (Instant Transaction Notification)
   * This is the authoritative payment confirmation from PayFast
   * 
   * Steps per PayFast documentation:
   * 1. Retrieve POST data and verify source IP
   * 2. Verify signature
   * 3. Confirm data received matches original transaction
   * 4. Confirm payment status
   * 5. Update database
   * 6. Reply with 200 OK
   */
  public function handleNotify(Request $request)
  {
    Log::info('PayFast ITN received', $request->all());
    
    try {
      // Step 1: Get POST data
      $pfData = $request->all();
      
      // Step 2: Verify source IP (PayFast IPs)
      if (!$this->verifyPayFastIP($request->ip())) {
        Log::warning('PayFast ITN from invalid IP', ['ip' => $request->ip()]);
        return response('Invalid source IP', 403);
      }
      
      // Step 3: Get tenant settings
      $merchant_id = \App\Models\Tenant\TenantSetting::getSetting('payfast_merchant_id');
      $passphrase = \App\Models\Tenant\TenantSetting::getEncryptedSetting('payfast_passphrase');
      $testingMode = \App\Models\Tenant\TenantSetting::getSetting('payfast_is_test') ?? true;
      
      // Step 4: Verify signature
      if (!$this->verifySignature($pfData, $passphrase)) {
        Log::warning('PayFast ITN signature verification failed', ['data' => $pfData]);
        return response('Invalid signature', 400);
      }
      
      // Step 5: Verify merchant ID
      if ($pfData['merchant_id'] != $merchant_id) {
        Log::warning('PayFast ITN merchant ID mismatch', [
          'received' => $pfData['merchant_id'],
          'expected' => $merchant_id
        ]);
        return response('Invalid merchant ID', 400);
      }
      
      // Step 6: Verify payment status
      $paymentStatus = $pfData['payment_status'];
      Log::info('PayFast payment status', ['status' => $paymentStatus, 'data' => $pfData]);
      
      // Step 7: Get invoice (remove UBX- prefix if present)
      $mPaymentId = $pfData['m_payment_id'];
      if (str_starts_with($mPaymentId, 'UBX-')) {
        $mPaymentId = substr($mPaymentId, 4);
      }
      
      $bookingInvoice = BookingInvoice::find($mPaymentId);
      
      if (!$bookingInvoice) {
        Log::warning('PayFast ITN: Invoice not found', ['m_payment_id' => $mPaymentId]);
        return response('Invoice not found', 404);
      }
      
      // Step 8: Verify amount matches (in cents, so multiply by 100)
      $expectedAmount = number_format($bookingInvoice->remaining_balance, 2, '.', '');
      $receivedAmount = number_format($pfData['amount_gross'], 2, '.', '');
      
      if ($expectedAmount != $receivedAmount) {
        Log::warning('PayFast ITN amount mismatch', [
          'expected' => $expectedAmount,
          'received' => $receivedAmount,
          'invoice_id' => $bookingInvoice->id
        ]);
        return response('Amount mismatch', 400);
      }

      
      $primaryGuest = $bookingInvoice->booking->bookingGuests->first();
      
      // Step 9: Process payment based on status
      if ($paymentStatus == 'COMPLETE') {
        // Payment successful - update invoice and create payment record
        \DB::transaction(function() use ($bookingInvoice, $pfData) {
          // Create or update payment record
          $payment = \App\Models\Tenant\InvoicePayment::firstOrCreate(
            [
              'booking_invoice_id' => $bookingInvoice->id,
              'amount' => $pfData['amount_gross'],
            ],
            [
              'property_id' => $bookingInvoice->property_id,
              'guest_id' => $primaryGuest->id ?? null,
              'payment_method' => 'payfast',
              'payment_date' => now(),
              'reference_number' => 'PF-' . $pfData['pf_payment_id'],
              'status' => 'completed',
              'notes' => 'Payment received via PayFast ITN',
              'recorded_by' => null, // System recorded
              'meta' => json_encode([
                'payfast_data' => $pfData,
                'processed_at' => now()->toDateTimeString(),
              ]),
            ]
          );
          
          // Update invoice status
          $bookingInvoice->update(['status' => 'paid']);
          
          // Update booking status if needed
          $booking = $bookingInvoice->booking;
          if ($booking && $booking->status !== 'completed') {
            $booking->update(['status' => 'confirmed']);
          }
        });

        // if in production send email else log email
        if (config('app.env') === 'production') {
          
          // send the payment confirmation email to guest
          \Mail::to($primaryGuest->email)->send(new \App\Mail\Tenant\PaymentReceiptEmail($payment, $bookingInvoice, $primaryGuest));
        }
        // else {

        // }
        
        Log::info('PayFast payment processed successfully', [
          'invoice_id' => $bookingInvoice->id,
          'pf_payment_id' => $pfData['pf_payment_id']
        ]);
      } else {
        // Payment failed or cancelled
        Log::warning('PayFast payment not complete', [
          'status' => $paymentStatus,
          'invoice_id' => $bookingInvoice->id
        ]);
        
        // Optionally create a failed payment record
        \App\Models\Tenant\InvoicePayment::create([
          'property_id' => selected_property_id(),
          'booking_invoice_id' => $bookingInvoice->id,
          'guest_id' => $bookingInvoice->booking->bookingGuests->first()->id ?? null,
          'amount' => $pfData['amount_gross'],
          'payment_method' => 'payfast',
          'payment_date' => now(),
          'reference_number' => 'PF-' . $pfData['pf_payment_id'],
          'status' => 'failed',
          'notes' => 'Payment failed: ' . $paymentStatus,
          'recorded_by' => null,
          'meta' => json_encode(['payfast_data' => $pfData]),
        ]);

        // if in production send email else log email
        if (config('app.env') === 'production') {
         // send the payment confirmation email to guest
          \Mail::to($primaryGuest->email)->send(new \App\Mail\Tenant\PaymentFailedEmail($payment, $bookingInvoice, $primaryGuest));
        }
      }
      
      // Step 10: Respond with 200 OK as required by PayFast
      return response('OK', 200);
      
    } catch (\Exception $e) {
      Log::error('PayFast ITN processing error: ' . $e->getMessage(), [
        'exception' => $e,
        'data' => $request->all()
      ]);
      return response('Error processing ITN', 500);
    }
  }
  
  /**
   * Verify PayFast signature per documentation
   */
  private function verifySignature($pfData, $passPhrase = null)
  {
    // Get the signature sent by PayFast
    $signature = $pfData['signature'];
    unset($pfData['signature']);
    
    // Generate our own signature
    $pfParamString = '';
    foreach ($pfData as $key => $val) {
      if ($val !== '') {
        $pfParamString .= $key . '=' . urlencode(trim($val)) . '&';
      }
    }
    
    // Remove last ampersand
    $pfParamString = substr($pfParamString, 0, -1);
    
    // Add passphrase if set
    if ($passPhrase !== null) {
      $pfParamString .= '&passphrase=' . urlencode(trim($passPhrase));
    }
    
    $calculatedSignature = md5($pfParamString);
    
    return ($calculatedSignature === $signature);
  }
  
  /**
   * Verify that the request comes from PayFast servers
   * PayFast IP ranges per documentation
   */
  private function verifyPayFastIP($sourceIP)
  {
    // PayFast IP addresses (check documentation for current list)
    $validHosts = [
      'www.payfast.co.za',
      'sandbox.payfast.co.za',
      'w1w.payfast.co.za',
      'w2w.payfast.co.za',
    ];
    
    $validIps = [];
    foreach ($validHosts as $host) {
      $ips = gethostbynamel($host);
      if ($ips !== false) {
        $validIps = array_merge($validIps, $ips);
      }
    }
    
    // Also allow specific IP ranges
    $validIps[] = '197.97.145.144';
    $validIps[] = '41.74.179.194';
    
    // In development/testing, you might want to allow localhost
    if (config('app.env') !== 'production') {
      $validIps[] = '127.0.0.1';
      $validIps[] = '::1';
    }
    
    return in_array($sourceIP, $validIps);
  }

  /**
   * @param array $data
   * @param null $passPhrase
   * @return string
   */
  private function generateSignature($data, $passPhrase = null) {
    // Create parameter string
    $pfOutput = '';
    foreach( $data as $key => $val ) {
        if($val !== '') {
            $pfOutput .= $key .'='. urlencode( trim( $val ) ) .'&';
        }
    }
    // Remove last ampersand
    $getString = substr( $pfOutput, 0, -1 );
    if( $passPhrase !== null ) {
        $getString .= '&passphrase='. urlencode( trim( $passPhrase ) );
    }
    return md5( $getString );
  }
}
