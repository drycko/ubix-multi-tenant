<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenantUserNotification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_user_id',
        'type',
        'title',
        'message',
        'icon',
        'link',
        'data',
        'is_read',
        'read_at',
        'scheduled_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'scheduled_at' => 'datetime'
    ];

    /**
     * Define notification types
     */
    const NOTIFICATION_TYPES = [
        'SYSTEM' => 'system',
        'BOOKING' => 'booking',
        'PAYMENT' => 'payment',
        'ROOM_CHANGE' => 'room_change',
        'MAINTENANCE' => 'maintenance',
        'HOUSEKEEPING' => 'housekeeping',
        'ALERT' => 'alert'
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to only include notifications of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include scheduled notifications.
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '>', now());
    }

    /**
     * Create a new notification.
     */
    public static function notify($userId, $type, $title, $message, $data = [], $options = [])
    {
        $notification = new static;
        $notification->tenant_user_id = $userId;
        $notification->type = $type;
        $notification->title = $title;
        $notification->message = $message;
        $notification->data = $data;
        
        // Optional parameters
        if (isset($options['icon'])) {
            $notification->icon = $options['icon'];
        }
        if (isset($options['link'])) {
            $notification->link = $options['link'];
        }
        if (isset($options['scheduled_at'])) {
            $notification->scheduled_at = $options['scheduled_at'];
        }
        
        $notification->save();

        return $notification;
    }

    /**
     * Get the notification icon class based on type.
     */
    public function getIconClass()
    {
        return match($this->type) {
            self::NOTIFICATION_TYPES['SYSTEM'] => 'fas fa-cog',
            self::NOTIFICATION_TYPES['BOOKING'] => 'fas fa-calendar-check',
            self::NOTIFICATION_TYPES['PAYMENT'] => 'fas fa-money-bill',
            self::NOTIFICATION_TYPES['ROOM_CHANGE'] => 'fas fa-exchange-alt',
            self::NOTIFICATION_TYPES['MAINTENANCE'] => 'fas fa-tools',
            self::NOTIFICATION_TYPES['HOUSEKEEPING'] => 'fas fa-broom',
            self::NOTIFICATION_TYPES['ALERT'] => 'fas fa-exclamation-circle',
            default => 'fas fa-bell'
        };
    }
}
