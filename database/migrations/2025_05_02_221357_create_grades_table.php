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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->UnsignedBigInteger('school_section_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->unique()->nullable();
            $table->integer('level')->nullable();
            $table->text('description')->nullable();
            $table->integer('capacity')->default(40);
            $table->integer('breakeven_number')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
