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
        Schema::create('interview_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_schedule_id')->constrained('interview_schedules')->onDelete('cascade');
            $table->foreignId('interviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('applicant_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('communication_score');
            $table->integer('technical_score');
            $table->integer('problem_solving_score');
            $table->integer('cultural_fit_score');
            $table->decimal('overall_score', 3, 2)->storedAs('(communication_score + technical_score + problem_solving_score + cultural_fit_score) / 4.0');
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->enum('recommendation', ['hire', 'no_hire', 'maybe', 'strong_hire'])->default('maybe');
            $table->text('next_steps')->nullable();
            $table->text('additional_notes')->nullable();
            $table->enum('feedback_status', ['draft', 'submitted', 'reviewed'])->default('draft');
            $table->timestamps();

            $table->index('interview_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_feedback');
    }
};
