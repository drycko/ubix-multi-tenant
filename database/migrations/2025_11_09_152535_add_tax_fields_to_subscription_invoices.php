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
        Schema::table('subscription_invoices', function (Blueprint $table) {
            // Tax calculation fields
            $table->decimal('subtotal_amount', 10, 2)->after('amount')->nullable();
            $table->decimal('tax_amount', 10, 2)->after('subtotal_amount')->default(0);
            $table->decimal('tax_rate', 8, 4)->after('tax_amount')->nullable();
            $table->string('tax_name')->after('tax_rate')->nullable();
            $table->enum('tax_type', ['percentage', 'fixed'])->after('tax_name')->nullable();
            $table->boolean('tax_inclusive')->after('tax_type')->default(false);
            $table->foreignId('tax_id')->after('tax_inclusive')->nullable()->constrained('taxes')->onDelete('set null');
            $table->string('currency')->after('tax_id')->default('ZAR');
            
            // Keep existing amount as total amount (subtotal + tax for non-inclusive, or same as subtotal for inclusive)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['tax_id']);
            // Then drop the columns
            $table->dropColumn([
                'subtotal_amount',
                'tax_amount', 
                'tax_rate',
                'tax_name',
                'tax_type',
                'tax_inclusive',
                'tax_id',
                'currency'
            ]);
        });
    }
};
