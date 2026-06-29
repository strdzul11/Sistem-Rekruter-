<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index()
    {
        $jobs = JobListing::with('creator')->orderBy('created_at', 'desc')->get();
        return view('admin.jobs.index', compact('jobs'));
    }

    public function create()
    {
        return view('admin.jobs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'position' => 'required|string|max:100',
            'company' => 'required|string|max:100',
            'location' => 'required|string|max:100',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'salary_range' => 'nullable|string|max:50',
            'employment_type' => 'required|in:full-time,part-time,contract,internship',
            'status' => 'required|in:active,inactive,closed',
        ]);

        $validated['created_by'] = Auth::id();

        JobListing::create($validated);

        return redirect()->route('admin.jobs.index')->with('success', 'Job listing created successfully.');
    }

    public function edit(JobListing $job)
    {
        return view('admin.jobs.edit', compact('job'));
    }

    public function update(Request $request, JobListing $job)
    {
        $validated = $request->validate([
            'position' => 'required|string|max:100',
            'company' => 'required|string|max:100',
            'location' => 'required|string|max:100',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'salary_range' => 'nullable|string|max:50',
            'employment_type' => 'required|in:full-time,part-time,contract,internship',
            'status' => 'required|in:active,inactive,closed',
        ]);

        $job->update($validated);

        return redirect()->route('admin.jobs.index')->with('success', 'Job listing updated successfully.');
    }

    public function destroy(JobListing $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.index')->with('success', 'Job listing deleted successfully.');
    }
}
