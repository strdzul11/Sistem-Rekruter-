<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->date('application_deadline')->nullable()->after('status');
            $table->unsignedInteger('applicant_quota')->nullable()->after('application_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn(['application_deadline', 'applicant_quota']);
        });
    }
};
