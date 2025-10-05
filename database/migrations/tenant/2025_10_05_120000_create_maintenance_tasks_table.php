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
        Schema::create('maintenance_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained('maintenance_requests')->cascadeOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete(); // Maintenance staff
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); // Manager/supervisor
            $table->enum('task_type', ['diagnosis', 'repair', 'replacement', 'testing', 'cleanup', 'documentation'])->default('repair');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled', 'on_hold'])->default('pending');
            $table->string('title');
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->json('tools_required')->nullable(); // JSON array of required tools
            $table->json('materials_used')->nullable(); // JSON array of materials/parts used
            $table->text('completion_notes')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->integer('estimated_minutes')->nullable();
            $table->integer('actual_minutes')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['maintenance_request_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['task_type', 'priority']);
            $table->index('scheduled_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_tasks');
    }
};