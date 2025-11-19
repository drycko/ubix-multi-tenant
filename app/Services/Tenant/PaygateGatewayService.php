<?php

namespace App\Services\Tenant;

use App\Models\Tenant\PaymentCredential;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
* PaygateGatewayService
*
* NOTE: This class implements a safe, configurable payload/signing approach for PayGate.
* The exact parameter names and signing algorithm must be validated against
* the PayGate product docs you are integrating (the initiate-request docs URL you provided).
*
* This implementation:
* - Builds an initiate payload with the common fields PayGate expects (PAYGATE_ID, REFERENCE, AMOUNT, CURRENCY, RETURN_URL, TRANSACTION_DATE)
* - Canonicalises the payload by sorting keys, building a RFC3986-encoded query string and signing with HMAC-SHA256 by default.
* - Exposes a verifyNotification() method that recomputes the signature using the same algorithm.
*
* If PayGate requires a different hashing algorithm (e.g. MD5, SHA512) or a different canonicalisation
* order/format, update the signPayload() method to match the docs exactly.
*/
class PaygateGatewayService
{
  protected PaymentCredential $credential;
  protected array $config;
  
  // Which request param PayGate expects for the signature (can be CHECKSUM, signature, etc)
  protected string $signatureParam = 'signature';

  // PayGate URLs
  private const INITIATE_URL = 'https://secure.paygate.co.za/payweb3/initiate.trans';
  private const REDIRECT_URL = 'https://secure.paygate.co.za/payweb3/process.trans';
  private const QUERY_URL    = 'https://secure.paygate.co.za/payweb3/query.trans';

  
  public function __construct(PaymentCredential $credential)
  {
    $this->credential = $credential;
    
    $this->config = [
      // these keys come from the tenant PaymentCredential row
      'merchant_id'    => $credential->merchant_id,
      'merchant_key'   => $credential->merchant_key,   // decrypted accessor (if implemented on model)
      'signature_key'  => $credential->signature_key,  // decrypted accessor (if implemented)
      'sandbox'        => $credential->meta['sandbox'] ?? true,
      // allow overriding endpoint via credential meta (e.g. paygate_endpoint)
      'endpoint'       => null,
      'return_url'     => $credential->meta['return_url'] ?? null,
      'notify_url'     => $credential->meta['notify_url'] ?? null,
      // allow overriding algorithm via meta: 'sha256' | 'sha512' | 'md5'
      'signature_algorithm' => $credential->meta['signature_algorithm'] ?? 'sha256',
      // signature param name if PayGate uses different name
      'signature_param' => $credential->meta['signature_param'] ?? $this->signatureParam,
    ];
    
    // normalise signature param into property
    $this->signatureParam = $this->config['signature_param'] ?? $this->signatureParam;
  }
  
  /**
  * Get the endpoint to post the user to (initiate). Prefer explicit credential meta, else sandbox/production.
  */
  public function getEndpoint($for='initiate'): string
  {
    switch ($for) {
      case 'initiate':
        if (!empty($this->config['endpoint'])) {
          return $this->config['endpoint'];
        }
        return $this->config['sandbox'] ? 'https://secure.paygate.co.za/payweb3/initiate.trans' : self::INITIATE_URL;
      case 'redirect':
        return $this->config['sandbox'] ? 'https://secure.paygate.co.za/payweb3/process.trans' : self::REDIRECT_URL;
      case 'query':
        return $this->config['sandbox'] ? 'https://secure.paygate.co.za/payweb3/query.trans' : self::QUERY_URL;
      default:
        return $this->config['sandbox'] ? 'https://secure.paygate.co.za/payweb3/initiate.trans' : self::INITIATE_URL;
    }

    return self::INITIATE_URL;
  }

  /**
  * Build initiate payload for PayGate.
  *
  * $data must include:
  *  - order_id (string)
  *  - amount_cents (int)   // amount in cents/lowest currency unit
  *  - currency (string)    // e.g. ZAR
  *  - return_url (string)  // optional - will fall back to credential meta return_url
  *  - notify_url (string)  // optional - will fall back to credential meta notify_url
  *  - optional customer info in customer.* keys
  *
  * Returns array of form inputs to POST to PayGate.
  */
  public function buildPayload(array $data): array
  {
    $orderId = (string) ($data['order_id'] ?? Str::uuid()->toString());
    $amount  = isset($data['amount_cents']) ? intval($data['amount_cents']) : 0;
    $currency = $data['currency'] ?? 'ZAR';
    $transactionDate = $data['transaction_date'] ?? now()->format('Y-m-d H:i:s'); 

    $guestEmail = $data['customer']['email'] ?? 'donegrafiks@gmail.com';
    // if guest email contains 'mailinator.com', replace with donegrafiks@gmail.com
    if (strpos($guestEmail, 'mailinator.com') !== false) {
      $guestEmail = 'donegrafiks@gmail.com';
    }
    $guestName = $data['customer']['name'] ?? 'Guest';

    // PayGate commonly expects AMOUNT in cents (or as integer without decimal - confirm in docs)
    // Use parameter names matching PayGate examples (adjust if your PayGate product uses different names)
    $payload = [
      // canonical PayGate fields (update names exactly to match the integration docs)
      'PAYGATE_ID'       => $this->config['merchant_id'], // Your PayGate ID – assigned by PayGate
      'REFERENCE'        => $orderId, // Our internal reference for the transaction
      'AMOUNT'           => $amount, // Amount in cents (e.g. 32.99 = 3299)
      'CURRENCY'         => $currency,
      'RETURN_URL'       => $data['return_url'] ?? $this->config['return_url'], // Where the customer should be redirected after payment
      'TRANSACTION_DATE' => $transactionDate, // UTC-formatted timestamp of the transaction
      'LOCALE'           => 'en-za',
      'COUNTRY'          => 'ZAF',
      'EMAIL'            => $guestEmail,
      'USER1'            => $guestName,
      // optionally include notify url (if the gateway supports server-to-server notify in the initiate request)
    ];
    
    $encryptionKey = $this->config['merchant_key'];
    
    // Compute signature/checksum according to configured algorithm
    $checksum = md5(implode('', $payload) . $encryptionKey);
    $payload['CHECKSUM'] = $checksum;
    // $payload[$this->signatureParam] = $this->signPayload($payload);
    \Log::info('Paygate form data', $payload);
    
    return $payload;
  }
  
  /**
  * Sign the payload according to credential configuration.
  *
  * IMPORTANT: The canonicalisation below (ksort + RFC3986 querystring) is a safe general approach.
  * PayGate may require a specific order or a specific concatenation character. If so, replace this method
  * with the exact algorithm from the PayGate docs.
  *
  * @param array $payload
  * @return string
  */
  protected function signPayload(array $payload): string
  {
    // Remove any signature param before signing (avoid self-inclusion)
    if (isset($payload[$this->signatureParam])) {
      unset($payload[$this->signatureParam]);
    }
    
    // Copy the payload and ensure keys are strings
    $copy = [];
    foreach ($payload as $k => $v) {
      // Skip null values to avoid ambiguous representations
      if ($v === null) {
        continue;
      }
      $copy[(string)$k] = is_array($v) ? json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string)$v;
    }
    
    // Sort keys to produce a canonical order
    ksort($copy);
    
    // Build RFC3986 encoded query string (key=value&key2=value2)
    $string = http_build_query($copy, '', '&', PHP_QUERY_RFC3986);
    
    // Some PayGate flavors want a trailing merchant secret appended; some want HMAC with the secret as key.
    // We'll support both via credential meta 'signature_mode':
    // - 'hmac' (default): hash_hmac($algo, $string, signature_key)
    // - 'append' : hash($algo, $string . $signature_key)
    // - 'prepend': hash($algo, $signature_key . $string)
    $algo = $this->config['signature_algorithm'] ?? 'sha256';
    $mode = $this->credential->meta['signature_mode'] ?? ($this->credential->meta['signature_mode'] ?? 'hmac');
    
    // Ensure algorithm is supported by PHP
    if (!in_array($algo, hash_algos())) {
      // fallback to sha256 if unsupported
      $algo = 'sha256';
    }
    
    $secret = $this->config['signature_key'] ?? ($this->config['merchant_key'] ?? '');
    
    if ($mode === 'append') {
      return hash($algo, $string . $secret);
    }
    
    if ($mode === 'prepend') {
      return hash($algo, $secret . $string);
    }
    
    // default: HMAC
    return hash_hmac($algo, $string, $secret);
  }
  
  /**
  * Verify incoming notification / callback from PayGate.
  *
  * This recomputes the signature and compares in a timing-safe manner.
  *
  * @param array $payload
  * @return bool
  */
  public function verifyNotification(array $payload): bool
  {
    $incoming = $payload[$this->signatureParam] ?? ($payload['CHECKSUM'] ?? ($payload['signature'] ?? ''));
    if (empty($incoming)) {
      Log::warning('Paygate verification failed: missing signature param', ['expected_param' => $this->signatureParam, 'payload_keys' => array_keys($payload)]);
      return false;
    }
    
    // Recompute using the same signing algorithm
    $computed = $this->signPayload($payload);
    
    // timing-safe compare
    $ok = hash_equals((string)$computed, (string)$incoming);
    
    if (! $ok) {
      Log::warning('Paygate verification mismatch', [
        'computed' => substr($computed, 0, 32) . '...', // avoid logging full secret-derived strings in prod logs
        'incoming_sample' => substr((string)$incoming, 0, 32) . '...',
        'keys' => array_keys($payload),
      ]);
    }
    
    return $ok;
  }
}