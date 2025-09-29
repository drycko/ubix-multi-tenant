<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            // First remove any invalid references
            DB::table('subscription_invoices')
                ->whereNotIn('tenant_id', DB::table('tenants')->pluck('id'))
                ->delete();
            
            DB::table('subscription_invoices')
                ->whereNotIn('subscription_id', DB::table('subscriptions')->pluck('id'))
                ->delete();

            // Now add the foreign key constraints
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->onDelete('cascade');

            $table->foreign('subscription_id')
                  ->references('id')
                  ->on('subscriptions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['subscription_id']);
        });
    }
};