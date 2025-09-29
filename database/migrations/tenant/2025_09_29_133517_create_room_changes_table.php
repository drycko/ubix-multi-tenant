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
        Schema::create('room_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('original_room_id')->constrained('rooms');
            $table->foreignId('new_room_id')->constrained('rooms');
            $table->foreignId('changed_by')->constrained('users');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamp('scheduled_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_changes');
    }
};
