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
        Schema::create('packages', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('property_id')->nullable(); // No foreign key constraint
            $table->foreignId('pkg_id')->nullable()->comment('original primary key name for import compatibility'); // Keep the original primary key name for import compatibility
            $table->string('pkg_name');
            $table->string('pkg_sub_title')->nullable();
            $table->text('pkg_description')->nullable();
            $table->integer('pkg_number_of_nights')->nullable();
            $table->string('pkg_checkin_days')->nullable()->comment('Comma-separated days: 1,2,3,4,5,6,7');
            $table->enum('pkg_status', ['active', 'inactive', 'draft'])->default('active');
            $table->foreignId('pkg_enterby')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('deleted')->default(false);
            $table->string('pkg_image')->nullable();
            
            // Additional useful fields for a SaaS
            $table->decimal('pkg_base_price', 12, 2)->nullable();
            $table->json('pkg_inclusions')->nullable()->comment('JSON array of included features');
            $table->json('pkg_exclusions')->nullable()->comment('JSON array of excluded features');
            $table->integer('pkg_min_guests')->default(1);
            $table->integer('pkg_max_guests')->nullable();
            $table->date('pkg_valid_from')->nullable();
            $table->date('pkg_valid_to')->nullable();
            
            $table->timestamps(); // Adds created_at and updated_at
            $table->softDeletes();
            
            $table->index('pkg_status');
            $table->index('deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
