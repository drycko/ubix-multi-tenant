<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentralSetting extends Model
{
    // Model properties can be defined here
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function deleteByKey($key)
    {
        return self::where('key', $key)->delete();
    }

    public static function allSettings()
    {
        return self::all()->pluck('value', 'key')->toArray();
    }

    public function __toString()
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    public function scopeNotKey($query, $key)
    {
        return $query->where('key', '!=', $key);
    }

    public function scopeWithValue($query)
    {
        return $query->whereNotNull('value')->where('value', '!=', '');
    }
}
