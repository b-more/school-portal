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
        Schema::table('salary_grades', function (Blueprint $table) {
            // Add foreign key to staff_designations table
            $table->foreignId('staff_designation_id')->nullable()->after('name')
                ->constrained('staff_designations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_grades', function (Blueprint $table) {
            $table->dropForeign(['staff_designation_id']);
            $table->dropColumn('staff_designation_id');
        });
    }
};
