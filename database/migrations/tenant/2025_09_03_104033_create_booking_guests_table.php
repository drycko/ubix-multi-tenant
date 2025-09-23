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
        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->nullable(); // No foreign key constraint
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_adult')->default(true)->comment('True for adult, false for child');
            $table->integer('age')->nullable()->comment('Age of guest, especially important for children');
            $table->boolean('is_sharing')->default(false);
            $table->text('special_requests')->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->text('payment_details')->nullable();
            $table->time('arrival_time')->nullable();
            $table->json('legacy_meta')->nullable()->comment('Stores CCTYPE, CCNAME, CCNO, CCEXPIRE, etc.');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['booking_id', 'guest_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_guests');
    }
};
