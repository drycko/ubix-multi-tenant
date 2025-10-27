<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('tenant')->create('guest_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->text('feedback');
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5 stars
            $table->string('status')->default('pending'); // pending, published, rejected
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('guest_feedback');
    }
};
