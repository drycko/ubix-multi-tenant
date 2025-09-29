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
        Schema::table('users', function (Blueprint $table) {
            // Add address field
            $table->string('address')->nullable()->after('phone');
            // add profile picture field
            $table->string('profile_picture')->nullable()->after('position');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the added fields
            $table->dropColumn(['address', 'profile_picture']);
        });
    }
};
