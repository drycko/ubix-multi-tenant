<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomChange extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'original_room_id',
        'new_room_id',
        'changed_by',
        'status',
        'reason',
        'notes',
        'scheduled_date',
        'completed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_date' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Valid statuses for room changes
     */
    const STATUSES = [
        'PENDING' => 'pending',
        'COMPLETED' => 'completed',
        'CANCELLED' => 'cancelled'
    ];

    /**
     * Get the booking associated with this room change.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the original room.
     */
    public function originalRoom()
    {
        return $this->belongsTo(Room::class, 'original_room_id');
    }

    /**
     * Get the new room.
     */
    public function newRoom()
    {
        return $this->belongsTo(Room::class, 'new_room_id');
    }

    /**
     * Get the user who made the change.
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Mark the room change as completed.
     */
    public function complete()
    {
        $this->update([
            'status' => self::STATUSES['COMPLETED'],
            'completed_at' => now()
        ]);
    }

    /**
     * Mark the room change as cancelled.
     */
    public function cancel()
    {
        $this->update([
            'status' => self::STATUSES['CANCELLED']
        ]);
    }

    /**
     * Check if the room change is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUSES['PENDING'];
    }

    /**
     * Check if the room change is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUSES['COMPLETED'];
    }

    /**
     * Check if the room change is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUSES['CANCELLED'];
    }

    /**
     * Scope a query to only include pending room changes.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUSES['PENDING']);
    }

    /**
     * Scope a query to only include completed room changes.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUSES['COMPLETED']);
    }

    /**
     * Scope a query to only include cancelled room changes.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUSES['CANCELLED']);
    }

    /**
     * Scope a query to only include scheduled room changes for a specific date.
     */
    public function scopeScheduledFor($query, $date)
    {
        return $query->whereDate('scheduled_date', $date);
    }
}
