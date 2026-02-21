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
        Schema::table('employees', function (Blueprint $table) {
            // Personal Details
            $table->date('date_of_birth')->nullable()->after('name');
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_of_birth');
            $table->string('marital_status')->nullable()->after('gender');
            $table->string('nationality')->default('Zambian')->after('marital_status');
            $table->text('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('province')->nullable()->after('city');

            // Statutory/Compliance Fields (Zambia)
            $table->string('nrc_number')->nullable()->after('province'); // National Registration Card
            $table->string('napsa_number')->nullable()->after('nrc_number'); // NAPSA Number
            $table->string('tpin_number')->nullable()->after('napsa_number'); // Tax PIN (ZRA)
            $table->string('nhima_number')->nullable()->after('tpin_number'); // Health Insurance

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            // Next of Kin
            $table->string('next_of_kin_name')->nullable();
            $table->string('next_of_kin_phone')->nullable();
            $table->string('next_of_kin_relationship')->nullable();
            $table->text('next_of_kin_address')->nullable();

            // Employment Details
            $table->enum('employment_type', ['permanent', 'contract', 'part_time', 'probation'])->default('permanent')->after('status');
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->string('termination_reason')->nullable();

            // Bank Details
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();

            // Qualifications
            $table->string('highest_qualification')->nullable();
            $table->string('qualification_institution')->nullable();
            $table->year('qualification_year')->nullable();
            $table->string('professional_certifications')->nullable();

            // Leave Balances (Annual allocation)
            $table->integer('annual_leave_days')->default(21);
            $table->integer('sick_leave_days')->default(30);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth', 'gender', 'marital_status', 'nationality',
                'address', 'city', 'province',
                'nrc_number', 'napsa_number', 'tpin_number', 'nhima_number',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
                'next_of_kin_name', 'next_of_kin_phone', 'next_of_kin_relationship', 'next_of_kin_address',
                'employment_type', 'contract_start_date', 'contract_end_date',
                'probation_end_date', 'confirmation_date', 'termination_date', 'termination_reason',
                'bank_name', 'bank_branch', 'bank_account_number', 'bank_account_name',
                'highest_qualification', 'qualification_institution', 'qualification_year', 'professional_certifications',
                'annual_leave_days', 'sick_leave_days'
            ]);
        });
    }
};
