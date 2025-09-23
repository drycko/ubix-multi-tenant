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
        Schema::table('guests', function (Blueprint $table) {
            // Add country_name column to guests table
            $table->string('country_name')->nullable()->after('nationality');
            // add emergency_contact_phone column to guests table
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            // Remove country_name column from guests table
            $table->dropColumn('country_name');
            // remove emergency_contact_phone column from guests table
            $table->dropColumn('emergency_contact_phone');
        });
    }
};
