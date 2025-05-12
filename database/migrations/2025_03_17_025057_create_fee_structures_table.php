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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();

            // Relationship fields
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('academic_year_id');

            // Fee fields
            $table->decimal('basic_fee', 10, 2);
            $table->json('additional_charges')->nullable();
            $table->decimal('total_fee', 10, 2);

            // Additional information
            $table->text('description')->nullable();
            $table->text('name')->nullable();
            $table->boolean('is_active')->default(true);

            // Timestamps
            $table->timestamps();

            // // Foreign keys
            // $table->foreign('grade_id')->references('id')->on('grades')
            //       ->onDelete('restrict')->onUpdate('cascade');

            // $table->foreign('term_id')->references('id')->on('terms')
            //       ->onDelete('restrict')->onUpdate('cascade');

            // $table->foreign('academic_year_id')->references('id')->on('academic_years')
            //       ->onDelete('restrict')->onUpdate('cascade');

            // Unique constraint to prevent duplicates
            $table->unique(['grade_id', 'term_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
