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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Annual Leave, Sick Leave
            $table->string('code')->unique(); // e.g., AL, SL
            $table->text('description')->nullable();
            $table->integer('default_days')->default(0); // Default allocation
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_documentation')->default(false);
            $table->boolean('is_active')->default(true);
            $table->enum('gender_specific', ['all', 'male', 'female'])->default('all');
            $table->integer('max_consecutive_days')->nullable();
            $table->integer('min_service_days')->default(0); // Min days employed to be eligible
            $table->boolean('carry_forward')->default(false);
            $table->integer('max_carry_forward_days')->default(0);
            $table->timestamps();
        });

        // Seed default leave types
        DB::table('leave_types')->insert([
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'description' => 'Paid annual vacation leave',
                'default_days' => 21,
                'is_paid' => true,
                'requires_documentation' => false,
                'is_active' => true,
                'gender_specific' => 'all',
                'max_consecutive_days' => 14,
                'min_service_days' => 90,
                'carry_forward' => true,
                'max_carry_forward_days' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Paid sick leave with medical certificate',
                'default_days' => 30,
                'is_paid' => true,
                'requires_documentation' => true,
                'is_active' => true,
                'gender_specific' => 'all',
                'max_consecutive_days' => null,
                'min_service_days' => 0,
                'carry_forward' => false,
                'max_carry_forward_days' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Maternity leave for female employees',
                'default_days' => 90,
                'is_paid' => true,
                'requires_documentation' => true,
                'is_active' => true,
                'gender_specific' => 'female',
                'max_consecutive_days' => 90,
                'min_service_days' => 180,
                'carry_forward' => false,
                'max_carry_forward_days' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PL',
                'description' => 'Paternity leave for male employees',
                'default_days' => 5,
                'is_paid' => true,
                'requires_documentation' => true,
                'is_active' => true,
                'gender_specific' => 'male',
                'max_consecutive_days' => 5,
                'min_service_days' => 90,
                'carry_forward' => false,
                'max_carry_forward_days' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Compassionate Leave',
                'code' => 'CL',
                'description' => 'Leave for bereavement or family emergencies',
                'default_days' => 5,
                'is_paid' => true,
                'requires_documentation' => false,
                'is_active' => true,
                'gender_specific' => 'all',
                'max_consecutive_days' => 5,
                'min_service_days' => 0,
                'carry_forward' => false,
                'max_carry_forward_days' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Study Leave',
                'code' => 'STL',
                'description' => 'Leave for examinations and study',
                'default_days' => 10,
                'is_paid' => true,
                'requires_documentation' => true,
                'is_active' => true,
                'gender_specific' => 'all',
                'max_consecutive_days' => 10,
                'min_service_days' => 365,
                'carry_forward' => false,
                'max_carry_forward_days' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unpaid Leave',
                'code' => 'UL',
                'description' => 'Leave without pay',
                'default_days' => 30,
                'is_paid' => false,
                'requires_documentation' => false,
                'is_active' => true,
                'gender_specific' => 'all',
                'max_consecutive_days' => 30,
                'min_service_days' => 180,
                'carry_forward' => false,
                'max_carry_forward_days' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
