<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterviewResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_schedule_id',
        'question_id',
        'response_text',
        'score',
        'notes',
    ];

    public function interviewSchedule()
    {
        return $this->belongsTo(InterviewSchedule::class, 'interview_schedule_id');
    }

    public function question()
    {
        return $this->belongsTo(InterviewQuestion::class, 'question_id');
    }
}
