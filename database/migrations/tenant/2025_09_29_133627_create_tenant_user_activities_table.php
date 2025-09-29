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
        Schema::create('tenant_user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_user_id')->constrained('users')->onDelete('cascade');
            $table->string('activity_type');
            $table->text('description')->nullable();
            $table->string('subject_type')->nullable(); // For polymorphic relation (e.g., Booking, Room, etc.)
            $table->unsignedBigInteger('subject_id')->nullable(); // For polymorphic relation
            $table->json('properties')->nullable(); // Store additional activity details
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_user_activities');
    }
};
