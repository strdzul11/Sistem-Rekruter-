<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InterviewSchedule;
use App\Models\JobListing;

class DashboardController extends Controller
{
    public function index()
    {
        return view('hrd.dashboard', [
            'applicationCount' => Application::count(),
            'interviewCount' => InterviewSchedule::count(),
            'activeJobCount' => JobListing::where('status', 'active')->count(),
        ]);
    }
}
