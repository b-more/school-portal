<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('cv_document')->nullable()->after('profile_photo');
            $table->string('police_clearance')->nullable()->after('cv_document');
            $table->string('teaching_license')->nullable()->after('police_clearance');
            $table->string('nrc_copy')->nullable()->after('teaching_license');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['cv_document', 'police_clearance', 'teaching_license', 'nrc_copy']);
        });
    }
};
