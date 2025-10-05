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
        Schema::create('room_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->enum('status', ['dirty', 'clean', 'inspected', 'maintenance', 'out_of_order'])->default('dirty');
            $table->enum('housekeeping_status', ['pending', 'in_progress', 'completed', 'inspected'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Staff member
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete(); // Inspector
            $table->text('notes')->nullable();
            $table->timestamp('status_changed_at')->useCurrent();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['room_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['assigned_to', 'housekeeping_status']);
            $table->index('status_changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_statuses');
    }
};
