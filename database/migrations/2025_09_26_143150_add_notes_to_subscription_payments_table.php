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
        Schema::table('subscription_payments', function (Blueprint $table) {
            // Add notes column to store additional information about the payment
            $table->text('notes')->nullable()->after('payment_method'); // optional field
            // add index to payment_date for faster queries
            $table->date('payment_date')->nullable()->after('notes');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            // Drop the notes column if it exists
            $table->dropColumn('notes');
            $table->dropIndex(['payment_date']);
            $table->dropColumn('payment_date');
        });
    }
};
