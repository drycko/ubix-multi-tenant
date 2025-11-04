<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['recorded_by']);
            // Make the column nullable
            $table->foreignId('recorded_by')->nullable()->change();
            // Re-add the foreign key with onDelete('set null')
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            // add meta field to store additional info about who recorded the payment if needed
            $table->json('meta')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropForeign(['recorded_by']);
            $table->foreignId('recorded_by')->nullable(false)->change();
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
            $table->dropColumn('meta');
        });
    }
};
