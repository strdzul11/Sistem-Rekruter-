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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('job_listings')->onDelete('cascade');
            $table->string('applicant_name', 100)->nullable();
            $table->string('applicant_email', 100)->nullable();
            $table->string('applicant_phone', 20)->nullable();
            $table->integer('applicant_age')->nullable();
            $table->enum('applicant_gender', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('work_experience', 50)->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume_path', 255)->nullable();
            $table->enum('status', ['pending', 'reviewed', 'accepted', 'rejected', 'interview_scheduled'])->default('pending');
            $table->enum('interview_status', ['not_scheduled', 'scheduled', 'completed', 'cancelled'])->default('not_scheduled');
            $table->enum('application_type', ['registered', 'quick_apply'])->default('registered');
            $table->timestamps();

            $table->unique(['user_id', 'job_id'], 'unique_registered_application');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
