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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('department')->nullable(); // 'ECL', 'Primary', 'Secondary'
            $table->string('grade')->nullable();
            $table->string('capacity')->nullable();
            $table->string('status')->default('active'); // 'active', 'inactive'
            $table->string('section')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
