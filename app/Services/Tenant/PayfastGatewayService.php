<?php
namespace App\Services\Tenant;

use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\BookingInvoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PayfastGatewayService
{
  /**
  * build payfast form for posting
  * @param array $paymentDetails
  * @param string $merchantId
  * @param string $merchantKey
  * @param string $passphrase
  * @param bool $testingMode
  * @return string
  */
  public function buildPayfastForm(BookingInvoice $bookingInvoice): string
  {
    $tenantSettings = TenantSetting::getSettings([
      'payfast_merchant_id',
      'payfast_merchant_key',
      'payfast_passphrase',
      'payfast_is_test',
    ]);
    
    $merchant_id = $tenantSettings['payfast_merchant_id'] ?? '';
    $merchant_key = TenantSetting::getEncryptedSetting('payfast_merchant_key');
    $passphrase = TenantSetting::getEncryptedSetting('payfast_passphrase');
    // $merchant_id = '10024789'; // temp hardcode for testing
    // $merchant_key = 'dtz5khr0cbz74'; // temp hardcode for testing
    // $passphrase = 'AnotidaL2022'; // temp hardcode for testing
    $testingMode = $tenantSettings['payfast_is_test'] ?? true;
    // Construct variables
    // $cartTotal = $bookingInvoice->remaining_amount; // This amount needs to be sourced from your application;
    $primaryGuest = $bookingInvoice->booking->bookingGuests->where('is_primary', true)->first()?->guest;
    // Set payment details
    $paymentDetails = [
        'amount' => $bookingInvoice->remaining_balance,
        'item_name' => 'Booking Invoice #' . $bookingInvoice->invoice_number,
        'name_first' => $primaryGuest->first_name,
        'name_last' => $primaryGuest->last_name,
        'email_address' => $primaryGuest->email,
        'cell_number' => $primaryGuest->phone,
        'm_payment_id' => $bookingInvoice->id,
    ];
    $cartTotal = $bookingInvoice->remaining_balance;

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
        // Transaction details
        'm_payment_id' => $paymentDetails['m_payment_id'],
        'amount' => number_format( sprintf( '%.2f', $cartTotal ), 2, '.', '' ),
        'item_name' => $paymentDetails['item_name']
    );

    \Log::info('Passphrase for signature: ' . $passphrase);

    $signature = $this->generateSignature($data, $passphrase);
    $data['signature'] = $signature;
    \Log::info('PayFast form data', $data);

    // If in testing mode make use of either sandbox.payfast.co.za or www.payfast.co.za
    $pfHost = $testingMode ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
    $htmlForm = '<form action="https://'.$pfHost.'/eng/process" method="post">';
    foreach($data as $name=> $value)
    {
      $htmlForm .= '<input name="'.$name.'" type="hidden" value=\''.$value.'\' />';
    }
    $htmlForm .= '<button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;">
                  Pay with PayFast
                  </button></form>';
    return $htmlForm;
  }
    
  /**
  * Initiate a PayFast payment.
  */
  public function initiatePayment(array $paymentDetails)
  {
    try {
      $tenantSettings = TenantSetting::getSettings([
        'payfast_merchant_id',
        'payfast_merchant_key',
        'payfast_passphrase',
        'payfast_is_test',
      ]);
      
      $merchant_id = $tenantSettings['payfast_merchant_id'] ?? '';
      $merchant_key = TenantSetting::getEncryptedSetting('payfast_merchant_key');
      $passphrase = TenantSetting::getEncryptedSetting('payfast_passphrase');
      $testingMode = $tenantSettings['payfast_is_test'] ?? true;
      
      $data = array(
        'merchant_id' => $merchant_id,
        'merchant_key' => $merchant_key,
        'return_url' => route('tenant.payfast.return'),
        'cancel_url' => route('tenant.payfast.cancel'),
        'notify_url' => route('tenant.payfast.notify'),
        'name_first' => $paymentDetails['name_first'],
        'name_last' => $paymentDetails['name_last'],
        'email_address' => $paymentDetails['email_address'],
        'cell_number' => $paymentDetails['cell_number'],
        'm_payment_id' => $paymentDetails['m_payment_id'],
        'amount' => number_format($paymentDetails['amount'], 2, '.', ''),
        'item_name' => $paymentDetails['item_name'],
      );
      $signature = $this->generateSignature($data, $passphrase);
      $data['signature'] = $signature;
      // If in testing mode make use of either sandbox.payfast.co.za or www.payfast.co.za
      $pfHost = $testingMode ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
      $payfastUrl = 'https://' . $pfHost . '/eng/process';
      // Log::info('PayFast payment data', $data);
      // post to PayFast
      $response = Http::asForm()->post($payfastUrl, $data);
      // log response for debugging
      // Log::info('PayFast response', ['status' => $response->status(), 'body' => $response->body()]);
      return $response->body();
    } catch (\Exception $e) {
      Log::error('Error initiating PayFast payment: ' . $e->getMessage());
      throw $e;
    }
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