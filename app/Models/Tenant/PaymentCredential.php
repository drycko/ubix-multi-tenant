<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentCredential extends Model
{
  protected $table = 'payment_credentials';
  
  // Use guarded to avoid mass-assignment issues, allow explicit fields via setters
  protected $guarded = ['id'];
  
  protected $casts = [
    'meta' => 'array',
    'active' => 'boolean',
  ];
  
  /**
  * Accessor: decrypt merchant_key_encrypted
  */
  public function getMerchantKeyAttribute()
  {
    if (empty($this->merchant_key_encrypted)) {
      return null;
    }
    
    try {
      return Crypt::decryptString($this->merchant_key_encrypted);
    } catch (\Throwable $e) {
      // don't leak secret details in logs; return null on failure
      return null;
    }
  }
  
  /**
  * Mutator: encrypt merchant_key on set
  */
  public function setMerchantKeyAttribute($value)
  {
    $this->attributes['merchant_key_encrypted'] = $value ? Crypt::encryptString($value) : null;
  }
  
  /**
  * Accessor: decrypt signature_key_encrypted
  */
  public function getSignatureKeyAttribute()
  {
    if (empty($this->signature_key_encrypted)) {
      return null;
    }
    
    try {
      return Crypt::decryptString($this->signature_key_encrypted);
    } catch (\Throwable $e) {
      return null;
    }
  }
  
  /**
  * Mutator: encrypt signature_key on set
  */
  public function setSignatureKeyAttribute($value)
  {
    $this->attributes['signature_key_encrypted'] = $value ? Crypt::encryptString($value) : null;
  }
  
  /**
  * Optional helper to mark gateway
  */
  public function setGatewayAttribute($value)
  {
    $this->attributes['gateway'] = $value;
  }
  
  /**
  * Optional helper method: create transient instance from array (useful in factory)
  */
  public static function fromArray(array $data): self
  {
    $inst = new self();
    foreach ($data as $k => $v) {
      // prefer setters where available
      if (in_array($k, ['merchant_key', 'signature_key'])) {
        $inst->{$k} = $v;
      } elseif ($k === 'meta') {
        $inst->meta = $v;
      } else {
        $inst->{$k} = $v;
      }
    }
    return $inst;
  }
}