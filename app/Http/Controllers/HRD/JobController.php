<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\SystemSetting;
use App\Services\AuditLogger;
use App\Services\PermissionChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index()
    {
        PermissionChecker::denyUnless('jobs.view');

        $jobs = JobListing::withCount('applications')->with('creator')->orderBy('created_at', 'desc')->get();

        return view('hrd.jobs.index', compact('jobs'));
    }

    public function create()
    {
        PermissionChecker::denyUnless('jobs.manage');

        return view('hrd.jobs.create');
    }

    public function store(Request $request)
    {
        PermissionChecker::denyUnless('jobs.manage');

        $validated = $this->validateJob($request, isCreate: true);
        $validated['created_by'] = Auth::id();
        $validated['company'] = SystemSetting::getVal('company_name', 'Perusahaan');

        $job = JobListing::create($validated);

        AuditLogger::log('create', 'jobs', "Lowongan dibuat: {$job->position}", JobListing::class, $job->id);

        return redirect()->route('hrd.jobs.index')->with('success', 'Lowongan berhasil ditambahkan.');
    }

    public function edit(JobListing $job)
    {
        PermissionChecker::denyUnless('jobs.manage');

        return view('hrd.jobs.edit', compact('job'));
    }

    public function update(Request $request, JobListing $job)
    {
        PermissionChecker::denyUnless('jobs.manage');

        $validated = $this->validateJob($request, isCreate: false);
        $validated['company'] = SystemSetting::getVal('company_name', $job->company);

        $job->update($validated);

        AuditLogger::log('update', 'jobs', "Lowongan diperbarui: {$job->position}", JobListing::class, $job->id);

        return redirect()->route('hrd.jobs.index')->with('success', 'Lowongan berhasil diperbarui.');
    }

    public function destroy(JobListing $job)
    {
        PermissionChecker::denyUnless('jobs.manage');

        $position = $job->position;
        $jobId    = $job->id;
        $job->delete();

        AuditLogger::log('delete', 'jobs', "Lowongan dihapus: {$position}", JobListing::class, $jobId);

        return redirect()->route('hrd.jobs.index')->with('success', 'Lowongan berhasil dihapus.');
    }

    private function validateJob(Request $request, bool $isCreate = true): array
    {
        return $request->validate([
            'position'             => 'required|string|max:100',
            'location'             => 'required|string|max:100',
            'description'          => 'required|string',
            'requirements'         => 'required|string',
            'salary_range'         => 'nullable|string|max:50',
            'employment_type'      => 'required|in:full-time,part-time,contract,internship',
            'status'               => 'required|in:active,inactive,closed',
            // Fitur #6: deadline & kuota
            'application_deadline' => $isCreate
                ? 'nullable|date|after_or_equal:today'
                : 'nullable|date',
            'applicant_quota'      => 'nullable|integer|min:1',
        ]);
    }
}
