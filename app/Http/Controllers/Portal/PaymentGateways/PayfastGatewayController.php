<?php
namespace App\Http\Controllers\Portal\PaymentGateways;

use App\Http\Controllers\Controller;
use App\Models\TenantAdmin;
use App\Models\SubscriptionInvoice;
use App\Services\PaymentGateways\PayfastGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class PayfastGatewayController extends Controller
{
  protected $payfastGatewayService;
  
  public function __construct(PayfastGatewayService $payfastGatewayService)
  {
    $this->payfastGatewayService = $payfastGatewayService;
  }
  
  /**
  * Initiate PayFast Payment
  */
  public function initiate(Request $request)
  {
    $invoiceId = $request->input('invoice_id');
    $subscriptionInvoice = SubscriptionInvoice::findOrFail($invoiceId);
    
    $payfastForm = $this->payfastGatewayService->buildPayfastForm($subscriptionInvoice);
    
    return response()->json([
      'success' => true,
      'form' => $payfastForm,
    ]);
  }
  
  /**
  * Handle PayFast Return
  *
  * The return URL is used to show the user the result of the payment process.
  * ITN/Notify is the authoritative source for confirming payment (server-to-server).
  * Here we attempt a lightweight verification (by checking invoice state) and redirect user accordingly.
  */
  public function handleReturn(Request $request)
  {
    try {
      // Try to find the invoice id that we passed through the payment form.
      // Common PayFast fields: m_payment_id (merchant payment id) or custom_str1
      $invoiceId = $request->input('m_payment_id') ?? $request->input('custom_str1');
      if (!$invoiceId) {
        Log::warning('PayFast return received without m_payment_id or custom_str1', ['payload' => $request->all()]);
        return redirect()->route('portal.dashboard')->with('error', 'Payment returned but could not identify invoice.');
      }
      
      $subscriptionInvoice = SubscriptionInvoice::find($invoiceId);
      if (!$subscriptionInvoice) {
        Log::warning('PayFast return: invoice not found', ['invoice_id' => $invoiceId, 'payload' => $request->all()]);
        return redirect()->route('portal.dashboard')->with('error', 'Payment returned but invoice was not found.');
      }
      
      // The reliable confirmation comes from the notify (ITN). If ITN already processed the invoice,
      // show success to the user; otherwise, show a pending message.
      $status = strtolower($subscriptionInvoice->status ?? '');
      if (in_array($status, ['paid', 'completed', 'paid_off', 'paid_success', 'successful'])) {
        return redirect()->route('portal.dashboard')->with('success', 'Payment completed successfully.');
      }
      
      // If invoice not yet marked as paid, show an informational message and mention that
      // payment will be confirmed via email once ITN is processed.
      return redirect()->route('portal.dashboard')->with('info', 'Payment returned. We are confirming the payment and will update your account shortly.');
    } catch (\Exception $e) {
      Log::error('Error handling PayFast return', ['exception' => $e, 'payload' => $request->all()]);
      return redirect()->route('portal.dashboard')->with('error', 'An error occurred while processing the payment return.');
    }
  }
  
  /**
  * Handle PayFast Cancel
  *
  * User cancelled the payment on the PayFast side and was redirected here.
  */
  public function handleCancel(Request $request)
  {
    // You can log details for troubleshooting
    Log::info('PayFast payment cancelled by user', ['payload' => $request->all()]);
    
    // Try to redirect back to a useful page (invoice or dashboard)
    $invoiceId = $request->input('m_payment_id') ?? $request->input('custom_str1');
    if ($invoiceId && $subscriptionInvoice = SubscriptionInvoice::find($invoiceId)) {
      return redirect()->route('portal.invoices.show', ['invoice' => $subscriptionInvoice->id])
      ->with('warning', 'Payment was cancelled. Your invoice remains unpaid.');
    }
    
    return redirect()->route('portal.dashboard')->with('warning', 'Payment was cancelled.');
  }
  
  /**
  * Handle PayFast Notify (ITN)
  *
  * Per PayFast docs (Step 4 - Confirm Payment) we must:
  * 1) POST all received data back to PayFast validate endpoint
  * 2) Verify PayFast responds VALID
  * 3) Verify the amount and merchant id/key if desired
  * 4) Verify payment_status == COMPLETE (or APPROVED depending on integration)
  * 5) Update database accordingly and respond HTTP 200
  */
  public function handleNotify(Request $request)
  {
    $payload = $request->all();
    Log::info('PayFast notify received', ['payload' => $payload]);
    
    // Basic check for merchant id/key (optional but good practice)
    $expectedMerchantId = config('services.payfast.merchant_id') ?? env('PAYFAST_MERCHANT_ID');
    $expectedMerchantKey = config('services.payfast.merchant_key') ?? env('PAYFAST_MERCHANT_KEY');
    
    $merchantIdMatches = true;
    $merchantKeyMatches = true;
    if ($expectedMerchantId && isset($payload['merchant_id'])) {
      $merchantIdMatches = ($payload['merchant_id'] == $expectedMerchantId);
    }
    if ($expectedMerchantKey && isset($payload['merchant_key'])) {
      $merchantKeyMatches = ($payload['merchant_key'] == $expectedMerchantKey);
    }
    
    if (! $merchantIdMatches || ! $merchantKeyMatches) {
      Log::warning('PayFast notify merchant id/key mismatch', [
        'received_merchant_id' => $payload['merchant_id'] ?? null,
        'expected_merchant_id' => $expectedMerchantId,
        'received_merchant_key' => $payload['merchant_key'] ?? null,
        'expected_merchant_key' => $expectedMerchantKey,
      ]);
      // Still respond 200 so PayFast won't keep retrying for a bad merchant id scenario,
      // but do not process the payment.
      return response('OK', 200);
    }
    
    // Validate data with PayFast by posting data back to their validate endpoint
    try {
      $isValid = $this->validateWithPayfast($payload);
    } catch (\Exception $e) {
      Log::error('PayFast notify validation request failed', ['exception' => $e, 'payload' => $payload]);
      // Return 200 to stop immediate retries, but it's up to you whether to return 500.
      return response('OK', 200);
    }
    
    if (! $isValid) {
      Log::warning('PayFast notify data invalid according to PayFast', ['payload' => $payload]);
      return response('OK', 200);
    }
    
    // Find the invoice: PayFast uses m_payment_id for merchant payment id (we should have set that)
    $invoiceId = $payload['m_payment_id'] ?? $payload['custom_str1'] ?? null;
    if (! $invoiceId) {
      Log::warning('PayFast notify missing m_payment_id/custom_str1', ['payload' => $payload]);
      return response('OK', 200);
    }
    
    $subscriptionInvoice = SubscriptionInvoice::find($invoiceId);
    if (! $subscriptionInvoice) {
      Log::warning('PayFast notify invoice not found', ['invoice_id' => $invoiceId, 'payload' => $payload]);
      return response('OK', 200);
    }
    
    // Confirm amount matches (be tolerant of formatting)
    $paidAmount = isset($payload['amount_gross']) ? (float) $payload['amount_gross'] : (isset($payload['amount']) ? (float)$payload['amount'] : null);
    // Try to detect invoice amount from common fields
    $invoiceAmount = $subscriptionInvoice->amount ?? $subscriptionInvoice->total ?? $subscriptionInvoice->gross_amount ?? null;
    
    if ($invoiceAmount !== null && $paidAmount !== null) {
      // Compare with small tolerance
      if (abs((float)$invoiceAmount - (float)$paidAmount) > 0.01) {
        Log::warning('PayFast notify amount mismatch', [
          'invoice_id' => $invoiceId,
          'invoice_amount' => $invoiceAmount,
          'paid_amount' => $paidAmount,
          'payload' => $payload,
        ]);
        // Don't mark paid if amounts mismatch
        return response('OK', 200);
      }
    }
    
    // Check payment status
    $paymentStatus = strtolower($payload['payment_status'] ?? '');
    if ($paymentStatus !== 'complete' && $paymentStatus !== 'paid' && $paymentStatus !== 'processing') {
      Log::info('PayFast notify received non-final payment status', ['invoice_id' => $invoiceId, 'payment_status' => $paymentStatus]);
      // For some integrations you may want to handle 'processing' or 'pending' differently.
      return response('OK', 200);
    }
    
    // At this point, the notification is valid and payment is complete => update invoice/subscription
    try {
      // Update subscription invoice
      $subscriptionInvoice->transaction_id = $payload['pf_payment_id'] ?? ($payload['payment_id'] ?? null);
      $subscriptionInvoice->status = 'paid'; // adapt to your app's status enums
      $subscriptionInvoice->paid_at = now();
      if (method_exists($subscriptionInvoice, 'save')) {
        $subscriptionInvoice->save();
      } else {
        Log::warning('SubscriptionInvoice model does not support save() in expected way', ['invoice_id' => $subscriptionInvoice->id]);
      }
      
      // Update tenant admin subscription status if possible
      // We try some conventional relations/fields, adapt to your models
      if (isset($subscriptionInvoice->tenant_admin_id)) {
        $tenantAdmin = TenantAdmin::find($subscriptionInvoice->tenant_admin_id);
        if ($tenantAdmin) {
          // Try to set a subscription status field if it exists
          if (isset($tenantAdmin->subscription_status)) {
            $tenantAdmin->subscription_status = 'active';
          }
          // Or set a boolean flag if applicable
          if (isset($tenantAdmin->subscription_active)) {
            $tenantAdmin->subscription_active = true;
          }
          // If you maintain subscription expiry, set accordingly here (not handled automatically)
          $tenantAdmin->save();
        }
      } elseif (method_exists($subscriptionInvoice, 'tenantAdmin')) {
        // If there is a relation defined on the model
        $tenantAdmin = $subscriptionInvoice->tenantAdmin;
        if ($tenantAdmin) {
          if (isset($tenantAdmin->subscription_status)) {
            $tenantAdmin->subscription_status = 'active';
          }
          if (isset($tenantAdmin->subscription_active)) {
            $tenantAdmin->subscription_active = true;
          }
          $tenantAdmin->save();
        }
      }
      
      Log::info('PayFast notify processed and invoice marked paid', ['invoice_id' => $subscriptionInvoice->id]);
    } catch (\Exception $e) {
      Log::error('Failed to update invoice/tenant after PayFast notify', ['exception' => $e, 'invoice_id' => $subscriptionInvoice->id, 'payload' => $payload]);
      // Still respond OK so PayFast does not keep retrying; you may want to trigger a background job or alert.
      return response('OK', 200);
    }
    
    // Respond 200 OK to PayFast to acknowledge receipt
    return response('OK', 200);
  }
  
  /**
  * Validate posted data with PayFast validate endpoint per PayFast docs.
  *
  * Returns true if PayFast responds "VALID", false otherwise.
  */
  protected function validateWithPayfast(array $payload): bool
  {
    // Build the validation URL depending on environment
    // Use environment config: PAYFAST_ENV = 'sandbox' or 'live' (default to live)
    $env = strtolower(env('PAYFAST_ENV', 'live'));
    if ($env === 'sandbox') {
      $validateUrl = 'https://sandbox.payfast.co.za/eng/query/validate';
    } else {
      $validateUrl = 'https://www.payfast.co.za/eng/query/validate';
    }
    
    // Remove signature from payload before sending back (if present)
    if (isset($payload['signature'])) {
      unset($payload['signature']);
    }
    
    // If you provided a passphrase when building the payment request, include it here too.
    // PayFast docs: When validating, include the passphrase if the merchant account uses one.
    $passphrase = config('services.payfast.passphrase') ?? env('PAYFAST_PASSPHRASE');
    if ($passphrase !== null && $passphrase !== '') {
      $payload['passphrase'] = $passphrase;
    }
    
    // Build url-encoded query
    $postString = http_build_query($payload, '', '&');
    
    // Use Laravel HTTP client for simplicity (or fallback to cURL)
    $response = Http::asForm()->timeout(30)->post($validateUrl, $payload);
    
    if ($response->failed()) {
      Log::error('PayFast validate request failed', ['validate_url' => $validateUrl, 'status' => $response->status(), 'body' => $response->body()]);
      throw new \RuntimeException('PayFast validation request failed with HTTP status ' . $response->status());
    }
    
    $body = trim($response->body());
    Log::debug('PayFast validate response', ['body' => $body]);
    
    return (strtoupper($body) === 'VALID');
  }
}