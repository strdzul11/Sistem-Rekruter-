<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'applicant_age',
        'applicant_gender',
        'work_experience',
        'cover_letter',
        'resume_path',
        'status',
        'interview_status',
        'application_type',
        'generated_letter_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }

    public function evaluations()
    {
        return $this->hasMany(ApplicationEvaluation::class);
    }

    public function ranking()
    {
        return $this->hasOne(ApplicationRanking::class);
    }

    public function interviewSchedules()
    {
        return $this->hasMany(InterviewSchedule::class);
    }
}
