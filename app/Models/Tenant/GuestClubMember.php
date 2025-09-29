<?php

namespace App\App\Models\Tenant\Tenant\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class GuestClubMember extends Model
{
    use HasFactory, SoftDeletes;, SoftDeletes
    // Fillable fields
    protected $fillable = [
        'guest_club_id',
        'guest_id',
    ];

    // Relationships
    public function guestClub()
    {
        return $this->belongsTo(GuestClub::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
