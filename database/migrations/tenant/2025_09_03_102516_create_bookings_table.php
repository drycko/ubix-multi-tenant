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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('bcode', 50)->unique()->comment('Legacy BCODE');
            $table->foreignId('room_id')->constrained();
            // property
            $table->unsignedBigInteger('property_id')->nullable(); // No foreign key constraint
            
            // Status and source
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('confirmed');
            $table->enum('source', ['website', 'walk_in', 'phone', 'agent', 'legacy'])->default('walk_in');
            
            // Dates
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->integer('nights');
            
            // Financials (from GSTACCHDG)
            $table->decimal('daily_rate', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->date('deposit_due_date')->nullable();
            $table->string('deposit_receipt_number')->nullable();
            $table->string('currency', 3)->default('ZAR');

            // Invoice details
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->decimal('invoice_amount', 12, 2)->nullable();
            
            // Legacy fields for import
            $table->integer('legacy_tr_id')->nullable()->comment('Original TR value for mapping');
            $table->string('legacy_group_id', 50)->nullable()->comment('Original GSTGROUP');
            $table->json('legacy_meta')->nullable()->comment('Stores EBY, EREF, PACKAGE, etc.');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index('bcode');
            $table->index('legacy_tr_id');
            $table->index(['arrival_date', 'departure_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
