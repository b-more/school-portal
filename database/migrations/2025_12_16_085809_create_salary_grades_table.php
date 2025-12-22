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
        Schema::create('salary_grades', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., SG1, SG2, SG3
            $table->string('name'); // e.g., Grade 1 - Entry Level
            $table->string('designation'); // e.g., Teacher, Senior Teacher, Head of Department
            $table->decimal('basic_salary', 12, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add salary_grade_id and designation_changed_date to employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('salary_grade_id')->nullable()->after('basic_salary')
                ->constrained('salary_grades')->nullOnDelete();
            $table->date('designation_changed_date')->nullable()->after('salary_grade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['salary_grade_id']);
            $table->dropColumn(['salary_grade_id', 'designation_changed_date']);
        });

        Schema::dropIfExists('salary_grades');
    }
};
