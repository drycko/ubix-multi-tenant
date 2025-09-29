<?php

namespace App\App\Models\Tenant\Tenant\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubMembershipBenefit extends Model
{
    use HasFactory, SoftDeletes;, SoftDeletes
    // Define fillable attributes
    protected $fillable = [
        'guest_club_id',
        'benefit_name',
        'benefit_description',
    ];

    // Relationships
    public function guestClub()
    {
        return $this->belongsTo(GuestClub::class);
    }
    
}
