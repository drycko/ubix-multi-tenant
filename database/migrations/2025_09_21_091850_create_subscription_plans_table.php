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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Basic, Pro, Enterprise
            $table->string('slug')->unique(); // e.g., basic, pro, enterprise
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->integer('max_properties')->default(1);
            $table->integer('max_users')->default(1);
            $table->integer('max_rooms')->default(10);
            $table->integer('max_guests')->default(100);
            $table->boolean('has_analytics')->default(false);
            $table->boolean('has_support')->default(false);
            $table->boolean('has_api_access')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable(); // JSON array of features
            $table->json('limitations')->nullable(); // JSON array of limitations
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
