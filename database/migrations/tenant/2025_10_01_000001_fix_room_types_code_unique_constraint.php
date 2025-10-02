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
        Schema::table('room_types', function (Blueprint $table) {
            // Drop the global unique constraint on code
            $table->dropUnique(['code']);
            
            // Add composite unique constraint for property_id and code
            $table->unique(['property_id', 'code'], 'room_types_property_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('room_types_property_code_unique');
            
            // Restore the original unique constraint on code only
            $table->unique('code');
        });
    }
};