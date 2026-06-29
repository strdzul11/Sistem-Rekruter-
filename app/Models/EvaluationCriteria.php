<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationCriteria extends Model
{
    use HasFactory;

    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'name',
        'description',
        'weight',
        'type',
        'min_value',
        'max_value',
        'is_active',
    ];

    public function evaluations()
    {
        return $this->hasMany(ApplicationEvaluation::class, 'criteria_id');
    }
}
