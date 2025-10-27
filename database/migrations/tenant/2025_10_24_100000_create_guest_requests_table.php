<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('tenant')->create('guest_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->string('type'); // e.g. room_service, feedback, maintenance, etc.
            $table->text('request');
            $table->string('status')->default('pending'); // pending, in_progress, completed, rejected
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('guest_requests');
    }
};
