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
        Schema::table('bookings', function (Blueprint $table) {
            // Update the enum values for the 'source' column
            $table->enum('source', ['website', 'walk_in', 'phone', 'agent', 'legacy', 'inhouse', 'wordpress', 'email'])->default('website')->change();
            // add IP address column
            $table->string('ip_address', 45)->nullable()->after('source'); // IPv6 max length is 45 characters
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Revert the enum values for the 'source' column
            $table->enum('source', ['website', 'walk_in', 'phone', 'agent', 'legacy'])->default('website')->change();
            // drop IP address column if exists
            $table->dropColumn('ip_address');
        });
    }
};
