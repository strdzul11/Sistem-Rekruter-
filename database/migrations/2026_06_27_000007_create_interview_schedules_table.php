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
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->foreignId('interviewer_id')->constrained('users')->onDelete('cascade');
            $table->enum('interview_type', ['phone', 'video', 'in-person', 'online'])->default('video');
            $table->dateTime('interview_date');
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->integer('duration_minutes')->default(60);
            $table->string('meeting_link', 500)->nullable();
            $table->string('meeting_room', 100)->nullable();
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->string('google_calendar_event_id', 255)->nullable();
            $table->string('outlook_calendar_event_id', 255)->nullable();
            $table->timestamps();

            $table->index('interview_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_schedules');
    }
};
