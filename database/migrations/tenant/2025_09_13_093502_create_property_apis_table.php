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
        Schema::create('property_apis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->nullable(); // No foreign key constraint
            $table->string('api_name')->nullable();
            $table->string('api_key')->unique();
            $table->string('api_secret')->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            // $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_apis');
    }
};
