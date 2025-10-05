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
        Schema::create('cleaning_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete(); // Specific to room type
            $table->string('name'); // Name of the checklist
            $table->text('description')->nullable();
            $table->enum('checklist_type', ['standard', 'checkout', 'deep_clean', 'maintenance', 'inspection'])->default('standard');
            $table->json('items'); // Array of checklist items with structure: [{item: '', required: true, completed: false}]
            $table->integer('estimated_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['property_id', 'checklist_type']);
            $table->index(['room_type_id', 'is_active']);
            $table->index(['checklist_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_checklists');
    }
};
