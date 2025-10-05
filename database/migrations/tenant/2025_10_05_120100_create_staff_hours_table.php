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
        Schema::create('staff_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Staff member
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->morphs('task'); // Can be maintenance_task_id, housekeeping_task_id, or other task types
            $table->enum('work_type', ['maintenance', 'housekeeping', 'inspection', 'administrative', 'other'])->default('maintenance');
            $table->text('description');
            $table->decimal('hours_worked', 5, 2); // Up to 999.99 hours
            $table->decimal('hourly_rate', 8, 2)->nullable(); // Rate at time of work
            $table->decimal('total_amount', 10, 2)->nullable(); // Calculated: hours * rate
            $table->date('work_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_overtime')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'work_date']);
            $table->index(['property_id', 'work_date']);
            // Note: task_type and task_id index is automatically created by morphs()
            $table->index(['work_type', 'work_date']);
            $table->index(['is_approved', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_hours');
    }
};