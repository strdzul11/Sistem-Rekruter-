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
        Schema::create('interview_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_schedule_id')->constrained('interview_schedules')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('interview_questions')->onDelete('cascade');
            $table->text('response_text')->nullable();
            $table->integer('score');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_responses');
    }
};
