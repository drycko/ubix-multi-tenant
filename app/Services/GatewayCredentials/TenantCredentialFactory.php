<?php

namespace App\Services\GatewayCredentials;

use App\Models\Tenant\PaymentCredential;
use App\Models\Tenant\TenantSetting;

/**
* Build a transient PaymentCredential model instance from TenantSetting.
* This avoids saving credentials again and allows PaygateGatewayService to accept
* a PaymentCredential instance as it expects.
*/
class TenantCredentialFactory
{
  /**
  * Build a PaymentCredential for the given tenant and gateway.
  *
  * @param  int|string  $tenantId
  * @param  string  $gateway   // 'paygate' or 'payfast' etc
  * @return \App\Models\Tenant\PaymentCredential
  */
  public static function makeForTenant($tenantId, string $gateway = 'paygate'): PaymentCredential
  {
    // load tenant settings (you already have TenantSetting::getSetting helpers)
    $settings = [
      'merchant_id'  => TenantSetting::getSetting($gateway . '_merchant_id', $tenantId),
      'is_test'      => TenantSetting::getSetting($gateway . '_is_test', $tenantId),
      'is_default'   => TenantSetting::getSetting($gateway . '_is_default', $tenantId),
      'merchant_key' => TenantSetting::getEncryptedSetting($gateway . '_merchant_key', $tenantId),
      'passphrase'   => TenantSetting::getEncryptedSetting($gateway . '_passphrase', $tenantId),
      // optional meta values you may want to set per-tenant in TenantSetting
      'endpoint'     => TenantSetting::getSetting($gateway . '_endpoint', $tenantId),
      'sandbox_endpoint' => TenantSetting::getSetting($gateway . '_sandbox_endpoint', $tenantId),
      'return_url'   => TenantSetting::getSetting($gateway . '_return_url', $tenantId),
      'notify_url'   => TenantSetting::getSetting($gateway . '_notify_url', $tenantId),
      'signature_algorithm' => TenantSetting::getSetting($gateway . '_signature_algorithm', $tenantId), // e.g. 'md5'|'sha256'
      'signature_mode' => TenantSetting::getSetting($gateway . '_signature_mode', $tenantId), // 'hmac'|'append'|'prepend'
      'signature_param' => TenantSetting::getSetting($gateway . '_signature_param', $tenantId), // e.g. 'CHECKSUM'
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