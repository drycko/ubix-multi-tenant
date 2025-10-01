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
        Schema::create('room_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained();
            $table->unsignedBigInteger('property_id')->nullable(); // Null or 0 for global room rates
            $table->string('name');
            $table->enum('rate_type', ['standard', 'off_season', 'package'])->default('standard');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->decimal('amount', 12, 2);
            $table->integer('min_nights')->nullable();
            $table->integer('max_nights')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_type_id', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_rates');
    }
};
