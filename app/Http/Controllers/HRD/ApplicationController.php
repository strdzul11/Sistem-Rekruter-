<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobListing;
use App\Services\AuditLogger;
use App\Services\LetterGenerator;
use App\Services\PermissionChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Concerns\DownloadsResume;

class ApplicationController extends Controller
{
    use DownloadsResume;
    public function index(Request $request)
    {
        PermissionChecker::denyUnless('applications.view');

        $jobs = JobListing::orderBy('position')->pluck('position', 'id');

        $applications = Application::with(['user', 'jobListing'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('job_id'), fn ($q) => $q->where('job_id', $request->job_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->q;
                $q->where(function ($sub) use ($search) {
                    $sub->where('applicant_name', 'like', "%{$search}%")
                        ->orWhere('applicant_email', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($uu) => $uu->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('hrd.applications.index', compact('applications', 'jobs'));
    }

    public function show(Application $application)
    {
        PermissionChecker::denyUnless('applications.view');
        $application->load(['user', 'jobListing', 'evaluations.criteria']);

        return view('hrd.applications.show', compact('application'));
    }

    public function update(Request $request, Application $application)
    {
        PermissionChecker::denyUnless('applications.manage');
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,accepted,rejected,interview_scheduled',
        ]);

        $application->update($validated);

        // Fitur #5: Generate surat otomatis saat accepted/rejected
        if (in_array($validated['status'], ['accepted', 'rejected'])) {
            try {
                $type = $validated['status'] === 'accepted' ? 'acceptance' : 'rejection';
                $letterPath = app(LetterGenerator::class)->generate($application, $type);
                $application->update(['generated_letter_path' => $letterPath]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Letter generation failed: ' . $e->getMessage());
            }
        }

        AuditLogger::log('update', 'applications', "Status lamaran diubah ke {$validated['status']}", Application::class, $application->id);

        return redirect()->route('hrd.applications.show', $application)
            ->with('success', 'Status lamaran berhasil diperbarui.');
    }

    /**
     * Fitur #2: Bulk update status beberapa lamaran sekaligus.
     */
    public function bulkUpdate(Request $request)
    {
        PermissionChecker::denyUnless('applications.manage');

        $validated = $request->validate([
            'application_ids'   => 'required|array|min:1',
            'application_ids.*' => 'exists:applications,id',
            'status'            => 'required|in:pending,reviewed,accepted,rejected,interview_scheduled',
        ]);

        $count = Application::whereIn('id', $validated['application_ids'])
            ->update(['status' => $validated['status']]);

        // Fitur #5: Generate surat untuk bulk accepted/rejected
        if (in_array($validated['status'], ['accepted', 'rejected'])) {
            $type = $validated['status'] === 'accepted' ? 'acceptance' : 'rejection';
            $apps = Application::whereIn('id', $validated['application_ids'])->get();
            foreach ($apps as $app) {
                try {
                    $letterPath = app(LetterGenerator::class)->generate($app, $type);
                    $app->update(['generated_letter_path' => $letterPath]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Letter generation failed for app #{$app->id}: " . $e->getMessage());
                }
            }
        }

        AuditLogger::log('update', 'applications', "Bulk update {$count} lamaran ke status {$validated['status']}");

        $statusLabel = [
            'pending'              => 'pending',
            'reviewed'             => 'ditinjau',
            'accepted'             => 'diterima',
            'rejected'             => 'ditolak',
            'interview_scheduled'  => 'interview dijadwalkan',
        ][$validated['status']];

        return redirect()->route('hrd.applications.index')
            ->with('success', "{$count} lamaran berhasil diubah menjadi '{$statusLabel}'.");
    }

    /**
     * Fitur #5: Download surat PDF yang sudah digenerate.
     */
    public function downloadLetter(Application $application)
    {
        PermissionChecker::denyUnless('applications.view');

        if (!$application->generated_letter_path || !Storage::exists($application->generated_letter_path)) {
            return redirect()->route('hrd.applications.show', $application)
                ->with('error', 'File surat tidak ditemukan.');
        }

        $filename = 'surat_' . ($application->applicant_name ?? $application->id) . '.pdf';

        return Storage::download($application->generated_letter_path, $filename);
    }

    public function preview(Application $application)
    {
        PermissionChecker::denyUnless('applications.view');
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
            'resume_url'      => $application->resume_path ? route('hrd.applications.resume', $application) : null,
            'detail_url'      => route('hrd.applications.show', $application),
        ]);
    }
}
