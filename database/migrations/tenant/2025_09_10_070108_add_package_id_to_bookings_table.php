<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add package_id column
            $table->unsignedBigInteger('package_id')->nullable()->after('room_id');
            // add is shared column
            $table->boolean('is_shared')->default(false)->after('package_id');
            // we need to update status enum to include 'booked'
            // remove existing status column
            $table->dropColumn('status');
            // add new status column with 'booked' option
            $table->enum('status', ['pending', 'booked', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('confirmed')->after('property_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop package_id column
            $table->dropColumn('package_id');
            // drop is shared column
            $table->dropColumn('is_shared');
            // drop status column with 'booked'
            $table->dropColumn('status');
            // add old status column back
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('confirmed')->after('property_id');
        });
    }
};
