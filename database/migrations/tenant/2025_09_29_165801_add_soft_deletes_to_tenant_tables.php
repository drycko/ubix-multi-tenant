<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tables that need soft deletes.
     *
     * @var array
     */
    protected $tables = [
        'booking_guests',
        'bookings',
        'rooms',
        'room_types',
        'room_amenities',
        'room_images',
        'packages',
        'properties',
        'guests',
        'guest_clubs',
        'guest_club_members',
        'club_membership_benefits',
        'room_changes',
        'tenant_settings',
        'tenant_user_activities',
        'tenant_user_notifications',
        'api_activities',
        'property_apis',
        'booking_invoices'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};