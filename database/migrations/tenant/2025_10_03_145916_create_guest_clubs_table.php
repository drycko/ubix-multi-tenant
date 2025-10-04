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
        Schema::create('guest_clubs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->nullable(); // No foreign key constraint
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Simple membership benefits as JSON
            $table->json('benefits')->nullable(); // e.g., {"discount_percentage": 10, "late_checkout": true, "complimentary_wifi": true}
            
            // Club level/tier (optional)
            $table->string('tier_level')->nullable(); // Bronze, Silver, Gold, VIP, etc.
            $table->integer('tier_priority')->default(0); // For ordering benefits by importance
            
            // Membership requirements (optional)
            $table->integer('min_bookings')->default(0); // Minimum bookings to qualify
            $table->decimal('min_spend', 10, 2)->default(0); // Minimum spend to qualify
            
            // Visual customization
            $table->string('badge_color', 7)->default('#3B82F6'); // Hex color for badges
            $table->string('icon')->nullable(); // FontAwesome icon class

            // Timestamps and Soft Deletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_clubs');
    }
};
