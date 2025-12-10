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
            $table->string('nrc')->nullable()->after('employee_id');
            $table->string('tpin')->nullable()->after('nrc');
            $table->string('account_number')->nullable()->after('tpin');
            $table->string('bank_name')->nullable()->after('account_number');
            $table->string('bank_branch')->nullable()->after('bank_name');
            $table->string('application_letter')->nullable()->after('profile_photo');
            $table->string('scanned_contract')->nullable()->after('application_letter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'nrc',
                'tpin',
                'account_number',
                'bank_name',
                'bank_branch',
                'application_letter',
                'scanned_contract',
            ]);
        });
    }
};
