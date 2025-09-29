<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivity extends Model
{
    // Model properties and relationships can be defined here
    protected $fillable = [
        'admin_id',
        'activity_type',
        'ip_address',
        'user_agent',
        'table_name',
        'record_id',
        'description',
        'is_read',
    ];

    protected $casts = [
        'admin_id' => 'integer',
        'record_id' => 'integer',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();
    }

    public function markAsUnread()
    {
        $this->is_read = false;
        $this->save();
    }
}
