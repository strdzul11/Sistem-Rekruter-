<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluationCriteria;
use App\Models\InterviewQuestion;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'userCount' => User::count(),
            'criteriaCount' => EvaluationCriteria::where('is_active', true)->count(),
            'questionCount' => InterviewQuestion::where('is_active', true)->count(),
        ]);
    }
}
