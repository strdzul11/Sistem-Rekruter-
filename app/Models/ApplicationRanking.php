<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationRanking extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'job_id',
        'saw_score',
        'rank_position',
        'evaluation_status',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }
}
