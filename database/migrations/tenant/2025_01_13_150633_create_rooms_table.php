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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained();
            $table->unsignedBigInteger('property_id')->nullable(); // NUll or 0 for global rooms
            $table->integer('legacy_room_code')->nullable()->comment('Original RMCODE value'); // For import mapping
            $table->string('number')->unique();
            $table->string('name')->nullable();
            $table->string('short_code')->nullable();
            $table->integer('floor')->nullable();
            $table->text('description')->nullable();
            $table->text('web_description')->nullable();
            $table->string('web_image')->nullable();
            $table->integer('display_order')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('legacy_room_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
