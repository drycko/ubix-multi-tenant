<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    // Model properties and relationships can be defined here
    protected $fillable = [
        'admin_id',
        'notification_type',
        'message',
        'ip_address',
        'user_agent',
        'is_read',
    ];

    protected $casts = [
        'admin_id' => 'integer',
        'is_read' => 'boolean',
    ];

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
