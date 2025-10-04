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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->string('property_id');
            $table->foreignId('booking_invoice_id')->constrained('booking_invoices')->onDelete('cascade');
            $table->string('payment_method')->nullable(); // cash, card, bank_transfer, check, etc.
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('reference_number')->nullable(); // transaction ref, check number, etc.
            $table->text('notes')->nullable();
            $table->string('status')->default('completed'); // completed, pending, failed
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade'); // user who recorded the payment
            $table->timestamps();

            // Add indexes
            $table->index(['property_id', 'booking_invoice_id']);
            $table->index(['payment_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};