<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterviewQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
        'question_type',
        'difficulty_level',
        'job_position',
        'is_active',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses()
    {
        return $this->hasMany(InterviewResponse::class, 'question_id');
    }
}
