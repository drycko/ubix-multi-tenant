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
        Schema::table('invoice_payments', function (Blueprint $table) {
            // Add guest_id column to link payment to a guest
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null')->after('property_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropColumn('guest_id');
        });
    }
};
