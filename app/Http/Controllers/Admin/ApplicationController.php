<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\DownloadsResume;
use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    use DownloadsResume;

    public function index()
    {
        $applications = Application::with(['user', 'jobListing'])->orderByDesc('created_at')->get();
        return view('admin.applications.index', compact('applications'));
    }

    public function show(Application $application)
    {
        $application->load(['user', 'jobListing', 'evaluations.criteria']);
        return view('admin.applications.show', compact('application'));
    }

    public function preview(Application $application)
    {
        $application->load(['user', 'jobListing', 'evaluations.criteria']);

        return response()->json([
            'applicant_name'  => $application->applicant_name ?? ($application->user?->name ?? '-'),
            'applicant_email' => $application->applicant_email ?? ($application->user?->email ?? '-'),
            'applicant_phone' => $application->applicant_phone ?? '-',
            'position'        => $application->jobListing->position,
            'company'         => $application->jobListing->company,
            'status'          => $application->status,
            'experience'      => $application->work_experience ?? '-',
            'cover_letter'    => $application->cover_letter ?? '',
            'resume_url'      => $application->resume_path ? route('admin.applications.resume', $application) : null,
            'detail_url'      => route('admin.applications.show', $application),
        ]);
    }
}
