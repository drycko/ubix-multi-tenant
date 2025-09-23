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
        Schema::create('club_membership_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_club_id')->constrained('guest_clubs')->onDelete('cascade');
            $table->string('benefit_name'); // Name of the benefit
            $table->text('benefit_description')->nullable(); // Description of the benefit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_membership_benefits');
    }
};
