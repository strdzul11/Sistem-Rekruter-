<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'criteria_id',
        'score',
        'evaluator_id',
        'notes',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function criteria()
    {
        return $this->belongsTo(EvaluationCriteria::class, 'criteria_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
