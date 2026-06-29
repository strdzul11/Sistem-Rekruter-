<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'position',
        'company',
        'location',
        'description',
        'requirements',
        'salary_range',
        'employment_type',
        'status',
        'created_by',
        'application_deadline',
        'applicant_quota',
    ];

    protected $casts = [
        'application_deadline' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'job_id');
    }

    /**
     * Sisa kuota pelamar. Return null jika kuota tidak diset.
     */
    public function remainingQuota(): ?int
    {
        if ($this->applicant_quota === null) {
            return null;
        }
        return max(0, $this->applicant_quota - $this->applications()->count());
    }

    /**
     * Apakah lowongan sudah lewat deadline.
     */
    public function isDeadlinePassed(): bool
    {
        return $this->application_deadline !== null && $this->application_deadline->isPast();
    }

    /**
     * Apakah kuota sudah penuh.
     */
    public function isQuotaFull(): bool
    {
        if ($this->applicant_quota === null) {
            return false;
        }
        return $this->applications()->count() >= $this->applicant_quota;
    }
}
