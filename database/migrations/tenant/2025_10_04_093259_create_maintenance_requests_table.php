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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete(); // Staff who reported
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Maintenance staff
            $table->string('request_number')->unique(); // Auto-generated request number
            $table->enum('category', ['plumbing', 'electrical', 'hvac', 'furniture', 'appliance', 'structural', 'other'])->default('other');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled', 'on_hold'])->default('pending');
            $table->string('title');
            $table->text('description');
            $table->text('location_details')->nullable(); // Specific location within room
            $table->json('images')->nullable(); // Array of image paths
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->text('parts_used')->nullable();
            $table->boolean('requires_room_closure')->default(false);
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['room_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['property_id', 'priority']);
            $table->index(['category', 'status']);
            $table->index('request_number');
            $table->index('scheduled_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
