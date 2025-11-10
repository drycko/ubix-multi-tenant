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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 8, 4); // Supports up to 9999.9999%
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->boolean('is_inclusive')->default(false); // Tax included in price or added to price
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['is_active']);
            $table->index(['display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};