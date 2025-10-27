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
  public function initiate(Request $request)
  {
    try {
      // validate request and get payment details (do these need to be in Model? Probably)
      $request->validate([
          'invoice_id' => 'required|integer|exists:booking_invoices,id',
          // Add other necessary validations
      ]);
      // validate invoice existence
      $invoiceId = $request->input('invoice_id');
      $bookingInvoice = \App\Models\Tenant\BookingInvoice::find($invoiceId);
      if (!$bookingInvoice) {
        \Log::warning('Attempt to pay a non-existent invoice. Invoice ID: ' . $invoiceId);
        return redirect()->back()->withErrors(['Invoice not found.']);
      }
      // invoice should have a remaining balance
      if ($bookingInvoice->remaining_balance <= 0) {
        \Log::warning('Attempt to pay an invoice with no remaining balance. Invoice ID: ' . $bookingInvoice->id);
        return redirect()->back()->withErrors(['Invoice has no remaining balance.']);
      }
      // Set payment details
      $paymentDetails = [
          'amount' => $bookingInvoice->remaining_balance,
          'item_name' => 'Booking Invoice #' . $bookingInvoice->invoice_number,
          'name_first' => $bookingInvoice->booking->bookingGuests->first()->first_name,
          'name_last' => $bookingInvoice->booking->bookingGuests->first()->last_name,
          'email_address' => $bookingInvoice->booking->bookingGuests->first()->email,
          'cell_number' => $bookingInvoice->booking->bookingGuests->first()->phone_number,
          'm_payment_id' => $bookingInvoice->id,
      ];
      // TODO: Validate request and get payment details
      $tenantSettings = \App\Models\Tenant\TenantSetting::getSettings([
          'payfast_merchant_id',
          'payfast_merchant_key',
          'payfast_passphrase',
          'payfast_is_test',
      ]);

      $merchant_id = $tenantSettings['payfast_merchant_id'] ?? '';
      $merchant_key = \App\Models\Tenant\TenantSetting::getEncryptedSetting('payfast_merchant_key');
      $passphrase = \App\Models\Tenant\TenantSetting::getEncryptedSetting('payfast_passphrase');
      $testingMode = $tenantSettings['payfast_is_test'] ?? true;

      // Construct variables
      $cartTotal = $paymentDetails['amount']; // This amount needs to be sourced from your application;
      $data = array(
          // Merchant details
          'merchant_id' => $merchant_id,
          'merchant_key' => $merchant_key,
          'return_url' => route('tenant.payfast.return'),
          'cancel_url' => route('tenant.payfast.cancel'),
          'notify_url' => route('tenant.payfast.notify'),
          // Buyer details
          'name_first' => $paymentDetails['name_first'],
          'name_last'  => $paymentDetails['name_last'],
          'email_address'=> $paymentDetails['email_address'],
          'cell_number'=> $paymentDetails['cell_number'],
          // Transaction details
          'm_payment_id' => 'UBX-' . $paymentDetails['m_payment_id'],
          'amount' => number_format( sprintf( '%.2f', $cartTotal ), 2, '.', '' ),
          'item_name' => $paymentDetails['item_name'],
      );

      $signature = $this->generateSignature($data, $passphrase);
      $data['signature'] = $signature;

      // If in testing mode make use of either sandbox.payfast.co.za or www.payfast.co.za
      // $testingMode = true;
      $pfHost = $testingMode ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
      $payfastUrl = 'https://' . $pfHost . '/eng/process'; // this only expects post data
      Log::info('PayFast payment data', $data);
      // post to PayFast
      $response = Http::asForm()->post($payfastUrl, $data);
      // log response for debugging
      Log::info('PayFast response', ['status' => $response->status(), 'body' => $response->body()]);
      return $response->body();

      // return Redirect::away($payfastUrl);
    } catch (\Exception $e) {
      Log::error('Error initiating PayFast payment: ' . $e->getMessage());
      return redirect()->back()->withErrors(['Error initiating payment.']);
    }
  }

  public function buildPayfastForm($data) {
    $formData = '<form action="https://'.$pfHost.'/eng/process" method="post">';
    foreach ($data as $key => $value) {
        $formData .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '"/>';
    }
    $formData .= '<button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;">
                  Pay with PayFast
                  </button>';
    return $formData;
  }

  // Handle PayFast return
  public function handleReturn(Request $request)
  {
      // TODO: Handle successful payment return
      $bookingInvoice = BookingInvoice::where('id', $request->input('m_payment_id'))->first();
      return view('tenant.payment.success', compact('bookingInvoice'));
  }

  // Handle PayFast cancel
  public function handleCancel(Request $request)
  {
      // TODO: Handle payment cancellation
      $bookingInvoice = BookingInvoice::where('id', $request->input('m_payment_id'))->first();
      return view('tenant.payment.cancel', compact('bookingInvoice'));
  }

  // Handle PayFast notify (IPN)
  public function handleNotify(Request $request)
  {
    // TODO: Validate IPN and update payment status
    Log::info('PayFast IPN received', $request->all());
    return response('OK', 200);
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
