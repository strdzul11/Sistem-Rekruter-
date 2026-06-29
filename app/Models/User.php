<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'address'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function applicantProfile()
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function jobListings()
    {
        return $this->hasMany(JobListing::class, 'created_by');
    }

    public function interviewSchedules()
    {
        return $this->hasMany(InterviewSchedule::class, 'interviewer_id');
    }

    public function calendarIntegrations()
    {
        return $this->hasMany(CalendarIntegration::class);
    }
}
