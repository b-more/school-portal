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
        Schema::create('parent_guardians', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('nrc')->nullable();
            $table->string('nationality')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->enum('relationship', ['father', 'mother', 'guardian', 'other'])->nullable();
            $table->string('occupation')->nullable();
            $table->string('address')->nullable();
            $table->UnsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('role_id')->default(4); // role_id 4 is for parent/guardian
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_guardians');
    }
};
