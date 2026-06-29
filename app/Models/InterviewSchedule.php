<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterviewSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'interviewer_id',
        'interview_type',
        'interview_date',
        'timezone',
        'duration_minutes',
        'meeting_link',
        'meeting_room',
        'status',
        'notes',
        'google_calendar_event_id',
        'outlook_calendar_event_id',
        'is_proposed',
        'selection_token',
    ];

    protected $casts = [
        'interview_date' => 'datetime',
        'is_proposed'    => 'boolean',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function feedback()
    {
        return $this->hasOne(InterviewFeedback::class, 'interview_schedule_id');
    }

    public function responses()
    {
        return $this->hasMany(InterviewResponse::class, 'interview_schedule_id');
    }
}
