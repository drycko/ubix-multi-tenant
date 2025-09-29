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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add meta_data column to store additional information as JSON (this is where we will store the subscription plan features in case they change over time this tenant can still have the old features)
            $table->json('meta_data')->nullable()->after('status'); // optional field
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop the meta_data column if it exists
            $table->dropColumn('meta_data');
        });
    }
};
