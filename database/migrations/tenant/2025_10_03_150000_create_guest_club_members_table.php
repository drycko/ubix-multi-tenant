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
        Schema::create('guest_club_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guest_club_id');
            $table->unsignedBigInteger('guest_id');
            $table->datetime('joined_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->text('notes')->nullable();

            // Foreign key constraints (if using them)
            // $table->foreign('guest_club_id')->references('id')->on('guest_clubs')->onDelete('cascade');
            // $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');

            // Prevent duplicate memberships
            $table->unique(['guest_club_id', 'guest_id']);

            // Timestamps and Soft Deletes
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['guest_club_id', 'status']);
            $table->index(['guest_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_club_members');
    }
};