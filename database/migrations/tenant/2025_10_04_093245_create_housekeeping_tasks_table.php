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
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete(); // Housekeeper
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); // Manager/supervisor
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete(); // Related booking if any
            $table->enum('task_type', ['cleaning', 'maintenance', 'inspection', 'deep_clean', 'setup'])->default('cleaning');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->json('checklist_items')->nullable(); // JSON array of checklist items
            $table->text('completion_notes')->nullable();
            $table->integer('estimated_minutes')->nullable();
            $table->integer('actual_minutes')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['room_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['property_id', 'scheduled_for']);
            $table->index(['task_type', 'priority']);
            $table->index('scheduled_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeping_tasks');
    }
};
