<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ClubMembershipBenefit extends Model
{
    use HasFactory;
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
