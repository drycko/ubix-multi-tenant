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
        Schema::table('users', function (Blueprint $table) {
            // Add missing fields that are in the User model but not in the original migration
            $table->string('profile_photo_path', 2048)->nullable()->after('address');
            
            // Add indexes for better performance
            $table->index('property_id');
            $table->index('role');
            $table->index('is_active');
            $table->index(['property_id', 'is_active']);
            
            // Note: No foreign key constraint for property_id to allow super users with null property_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['property_id', 'is_active']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['role']);
            $table->dropIndex(['property_id']);
            
            // Drop the added columns
            // $table->dropColumn(['address', 'profile_photo_path']);
        });
    }
};