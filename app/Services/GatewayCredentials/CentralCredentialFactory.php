<?php

namespace App\Services\GatewayCredentials;

use App\Models\PaymentCredential;
use App\Models\CentralSetting;

/**
* Build a transient PaymentCredential model instance from CentralSetting.
* This avoids saving credentials again and allows PaygateGatewayService to accept
* a PaymentCredential instance as it expects.
*/
class CentralCredentialFactory
{
  /**
  * Build a PaymentCredential for the given Central and gateway.
  *
  * @param  int|string  $centralId
  * @param  string  $gateway   // 'paygate' or 'payfast' etc
  * @return \App\Models\Central\PaymentCredential
  */
  public static function makeForCentral($centralId, string $gateway = 'paygate'): PaymentCredential
  {
    // load tenant settings (you already have CentralSetting::getSetting helpers)
    $settings = [
      'merchant_id'  => CentralSetting::getSetting($gateway . '_merchant_id', $centralId),
      'is_test'      => CentralSetting::getSetting($gateway . '_is_test', $centralId),
      'is_default'   => CentralSetting::getSetting($gateway . '_is_default', $centralId),
      'merchant_key' => CentralSetting::getEncryptedSetting($gateway . '_merchant_key', $centralId),
      'passphrase'   => CentralSetting::getEncryptedSetting($gateway . '_passphrase', $centralId),
      // optional meta values you may want to set per-tenant in CentralSetting
      'endpoint'     => CentralSetting::getSetting($gateway . '_endpoint', $centralId),
      'sandbox_endpoint' => CentralSetting::getSetting($gateway . '_sandbox_endpoint', $centralId),
      'return_url'   => CentralSetting::getSetting($gateway . '_return_url', $centralId),
      'notify_url'   => CentralSetting::getSetting($gateway . '_notify_url', $centralId),
      'signature_algorithm' => CentralSetting::getSetting($gateway . '_signature_algorithm', $centralId), // e.g. 'md5'|'sha256'
      'signature_mode' => CentralSetting::getSetting($gateway . '_signature_mode', $centralId), // 'hmac'|'append'|'prepend'
      'signature_param' => CentralSetting::getSetting($gateway . '_signature_param', $centralId), // e.g. 'CHECKSUM'
    ];
    
    // Create a transient PaymentCredential model instance (not saved)
    $cred = new PaymentCredential();
    
    // Basic fields
    $cred->merchant_id = $settings['merchant_id'] ?? null;
    
    // Use the model's mutator to set encrypted fields (if implemented)
    if (! empty($settings['merchant_key'])) {
      $cred->merchant_key = $settings['merchant_key'];
    }
    
    if (! empty($settings['passphrase'])) {
      // map passphrase to signature_key field expected by Paygate service
      $cred->signature_key = $settings['passphrase'];
    }
    
    // Build meta array
    $cred->meta = array_filter([
      'sandbox' => $settings['is_test'] ?? true,
      'endpoint' => $settings['endpoint'] ?: null,
      'sandbox_endpoint' => $settings['sandbox_endpoint'] ?: null,
      'return_url' => $settings['return_url'] ?: null,
      'notify_url' => $settings['notify_url'] ?: null,
      'signature_algorithm' => $settings['signature_algorithm'] ?: null,
      'signature_mode' => $settings['signature_mode'] ?: null,
      'signature_param' => $settings['signature_param'] ?: null,
    ]);
    
    // marque gateway for debugging/logging
    $cred->gateway = $gateway;
    
    return $cred;
  }
}