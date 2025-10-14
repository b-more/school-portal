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
        Schema::create('bus_fare_structures', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->enum('payment_plan', ['monthly', 'per_term'])->default('per_term');
            $table->decimal('monthly_amount', 10, 2)->nullable();
            $table->decimal('term_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('route_name');
            $table->index('payment_plan');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_fare_structures');
    }
};
