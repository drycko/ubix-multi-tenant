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
        Schema::table('central_settings', function (Blueprint $table) {
            // Add is_serialized column
            $table->boolean('is_serialized')->default(false)->after('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('central_settings', function (Blueprint $table) {
            // Remove is_serialized column
            $table->dropColumn('is_serialized');
        });
    }
};
