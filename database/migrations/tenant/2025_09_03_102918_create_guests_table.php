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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->nullable(); // No foreign key constraint
            $table->string('title', 25)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('id_number', 50)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->text('physical_address')->nullable();
            $table->text('residential_address')->nullable();
            $table->text('medical_notes')->nullable();
            $table->text('dietary_preferences')->nullable();
            $table->string('gown_size', 10)->nullable();
            $table->string('car_registration', 50)->nullable();
            $table->json('legacy_meta')->nullable()->comment('Stores OTH1, OTH2, OTH3, OTH4, TREATMENT, etc.');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['first_name', 'last_name']);
            $table->index('email');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
