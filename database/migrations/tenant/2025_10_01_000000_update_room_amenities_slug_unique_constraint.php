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
        Schema::table('room_amenities', function (Blueprint $table) {
            // Drop the existing unique constraint on slug
            $table->dropUnique(['slug']);
            
            // Add composite unique constraint for property_id and slug
            $table->unique(['property_id', 'slug'], 'room_amenities_property_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_amenities', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('room_amenities_property_slug_unique');
            
            // Restore the original unique constraint on slug only
            $table->unique('slug');
        });
    }
};