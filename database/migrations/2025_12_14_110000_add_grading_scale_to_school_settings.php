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
        Schema::table('school_settings', function (Blueprint $table) {
            // Zambian Primary School Grading Scale (ECZ Standard)
            $table->integer('grade_a_min')->default(80)->after('exam_weight_percentage');  // A: 80-100 (Distinction)
            $table->integer('grade_b_min')->default(65)->after('grade_a_min');              // B: 65-79 (Merit)
            $table->integer('grade_c_min')->default(50)->after('grade_b_min');              // C: 50-64 (Credit)
            $table->integer('grade_d_min')->default(40)->after('grade_c_min');              // D: 40-49 (Pass)
            $table->integer('grade_e_min')->default(0)->after('grade_d_min');               // E: 0-39 (Fail)

            // Grade descriptors/remarks
            $table->string('grade_a_remark')->default('Distinction')->after('grade_e_min');
            $table->string('grade_b_remark')->default('Merit')->after('grade_a_remark');
            $table->string('grade_c_remark')->default('Credit')->after('grade_b_remark');
            $table->string('grade_d_remark')->default('Pass')->after('grade_c_remark');
            $table->string('grade_e_remark')->default('Fail')->after('grade_d_remark');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'grade_a_min',
                'grade_b_min',
                'grade_c_min',
                'grade_d_min',
                'grade_e_min',
                'grade_a_remark',
                'grade_b_remark',
                'grade_c_remark',
                'grade_d_remark',
                'grade_e_remark',
            ]);
        });
    }
};
