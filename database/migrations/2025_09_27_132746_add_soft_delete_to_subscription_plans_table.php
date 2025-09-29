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
        Schema::table('subscription_plans', function (Blueprint $table) {
            // add soft deletes to subscription_plans table
            $table->softDeletes();
        });

        // add soft deletes to subscriptions table as well
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // remove soft deletes from subscription_plans table
            $table->dropSoftDeletes();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
