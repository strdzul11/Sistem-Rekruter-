<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterviewFeedback extends Model
{
    use HasFactory;

    protected $table = 'interview_feedback';

    protected $fillable = [
        'interview_schedule_id',
        'interviewer_id',
        'applicant_id',
        'communication_score',
        'technical_score',
        'problem_solving_score',
        'cultural_fit_score',
        'strengths',
        'weaknesses',
        'areas_for_improvement',
        'recommendation',
        'next_steps',
        'additional_notes',
        'feedback_status',
    ];

    public function interviewSchedule()
    {
        return $this->belongsTo(InterviewSchedule::class, 'interview_schedule_id');
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }
}
