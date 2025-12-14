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
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('school_section_id')
                ->nullable()
                ->after('role_id')
                ->constrained()
                ->onDelete('set null');

            $table->index('school_section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['school_section_id']);
            $table->dropColumn('school_section_id');
        });
    }
};
