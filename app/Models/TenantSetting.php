<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    // The table associated with the model.
    protected $table = 'tenant_settings';

    // The attributes that are mass assignable.
    protected $fillable = [
        'setting_key',
        'setting_value',
        'is_serialized',
    ];

    // Accessors & Mutators
    public function getSettingValueAttribute($value)
    {
        return $this->is_serialized ? unserialize($value) : $value;
    }

    public function setSettingValueAttribute($value)
    {
        $this->attributes['setting_value'] = $this->is_serialized ? serialize($value) : $value;
    }

    // Additional methods for managing settings can be added here.
    public static function getSetting($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    public static function setSetting($key, $value, $isSerialized = false)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $isSerialized ? serialize($value) : $value, 'is_serialized' => $isSerialized]
        );
    }
}
