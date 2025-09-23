<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabasePool extends Model
{
    // Table name
    protected $table = 'database_pool';

    // Fillable fields
    protected $fillable = [
        'database_name',
        'is_available',
        'assigned_to_tenant',
    ];

    // Casts
    protected $casts = [
        'is_available' => 'boolean',
    ];

    // Scope to get available databases
    public function scopeAvailable($query)
    {   
        // we can also check for null assigned_to_tenant if needed
        return $query->where('is_available', true)->whereNull('assigned_to_tenant');
    }

    // Mark database as assigned
    public function assignToTenant(string $tenantId): void
    {
        $this->is_available = false;
        $this->assigned_to_tenant = $tenantId;
        $this->save();
    }

    // Mark database as available
    public function markAsAvailable(): void
    {
        $this->is_available = true;
        $this->assigned_to_tenant = null;
        $this->save();
    }

    // Static method to get next available database
    public static function getNextAvailableDatabase(): ?string
    {
        $db = self::available()->first();
        return $db ? $db->database_name : null;
    }
}
