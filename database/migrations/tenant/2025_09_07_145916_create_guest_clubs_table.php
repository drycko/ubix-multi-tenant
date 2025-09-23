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
        Schema::create('guest_clubs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->nullable(); // No foreign key constraint
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // club members relationship will be handled in a separate table
            // club membership benefits can be a JSON field or a separate table based on requirements

            // Timestamps and Soft Deletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_clubs');
    }
};
