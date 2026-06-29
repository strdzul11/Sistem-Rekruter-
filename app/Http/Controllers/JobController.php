<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    /**
     * Landing page — public homepage with latest jobs.
     */
    public function landing()
    {
        $recentJobs = JobListing::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('welcome', compact('recentJobs'));
    }

    /**
     * Public jobs listing — all active jobs.
     */
    public function index()
    {
        $jobs = JobListing::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('jobs.index', compact('jobs'));
    }

    /**
     * Job detail page — shows full description + two apply options.
     */
    public function show(JobListing $job)
    {
        return view('jobs.show', compact('job'));
    }

    /**
     * Quick apply (guest, no login required).
     * Fitur #6: Tambah guard deadline/kuota dalam DB transaction.
     */
    public function quickApply(Request $request, JobListing $job)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'required|email|max:100',
            'phone'           => 'required|string|max:20',
            'age'             => 'nullable|integer|min:18|max:65',
            'gender'          => 'nullable|in:Laki-laki,Perempuan',
            'work_experience' => 'nullable|string|max:50',
            'cover_letter'    => 'nullable|string|max:2000',
            'resume'          => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        // Check duplicate email for same job
        $exists = Application::where('applicant_email', $validated['email'])
            ->where('job_id', $job->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['email' => 'Email ini sudah pernah melamar untuk posisi ini.'])->withInput();
        }

        // Handle file upload before transaction
        $resumePath = $request->file('resume')->store('resumes', 'local');

        try {
            DB::transaction(function () use ($job, $validated, $resumePath) {
                // Lock the job row to prevent race conditions
                $lockedJob = JobListing::lockForUpdate()->findOrFail($job->id);

                // Guard: status
                if ($lockedJob->status !== 'active') {
                    throw new \RuntimeException('Lowongan ini sudah ditutup.');
                }

                // Guard: deadline
                if ($lockedJob->application_deadline !== null && $lockedJob->application_deadline->isPast()) {
                    $lockedJob->update(['status' => 'closed']);
                    throw new \RuntimeException('Batas waktu pendaftaran untuk lowongan ini sudah berakhir.');
                }

                // Guard: kuota
                if ($lockedJob->applicant_quota !== null && $lockedJob->applications()->count() >= $lockedJob->applicant_quota) {
                    $lockedJob->update(['status' => 'closed']);
                    throw new \RuntimeException('Kuota pelamar untuk posisi ini sudah penuh.');
                }

                Application::create([
                    'job_id'           => $lockedJob->id,
                    'user_id'          => null,
                    'applicant_name'   => $validated['name'],
                    'applicant_email'  => $validated['email'],
                    'applicant_phone'  => $validated['phone'],
                    'applicant_age'    => $validated['age'] ?? null,
                    'applicant_gender' => $validated['gender'] ?? null,
                    'work_experience'  => $validated['work_experience'] ?? null,
                    'cover_letter'     => $validated['cover_letter'] ?? null,
                    'resume_path'      => $resumePath,
                    'status'           => 'pending',
                    'application_type' => 'quick_apply',
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('jobs.show', $job)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Lamaran Anda berhasil dikirim! Tim HR kami akan segera meninjau dan menghubungi Anda melalui email atau WhatsApp.');
    }

    /**
     * Apply as a logged-in user (registered applicant).
     * Fitur #6: Tambah guard deadline/kuota dalam DB transaction.
     */
    public function apply(Request $request, JobListing $job)
    {
        $user = Auth::user();

        // Check duplicate application
        $exists = Application::where('user_id', $user->id)
            ->where('job_id', $job->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['duplicate' => 'Anda sudah pernah melamar untuk posisi ini.'])->withInput();
        }

        $validated = $request->validate([
            'cover_letter' => 'nullable|string|max:2000',
            'resume'       => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $resumePath = $request->file('resume')->store('resumes', 'local');

        try {
            DB::transaction(function () use ($job, $user, $validated, $resumePath) {
                $lockedJob = JobListing::lockForUpdate()->findOrFail($job->id);

                if ($lockedJob->status !== 'active') {
                    throw new \RuntimeException('Lowongan ini sudah ditutup.');
                }

                if ($lockedJob->application_deadline !== null && $lockedJob->application_deadline->isPast()) {
                    $lockedJob->update(['status' => 'closed']);
                    throw new \RuntimeException('Batas waktu pendaftaran untuk lowongan ini sudah berakhir.');
                }

                if ($lockedJob->applicant_quota !== null && $lockedJob->applications()->count() >= $lockedJob->applicant_quota) {
                    $lockedJob->update(['status' => 'closed']);
                    throw new \RuntimeException('Kuota pelamar untuk posisi ini sudah penuh.');
                }

                Application::create([
                    'job_id'           => $lockedJob->id,
                    'user_id'          => $user->id,
                    'applicant_name'   => $user->name,
                    'applicant_email'  => $user->email,
                    'applicant_phone'  => $user->phone ?? null,
                    'cover_letter'     => $validated['cover_letter'] ?? null,
                    'resume_path'      => $resumePath,
                    'status'           => 'pending',
                    'application_type' => 'registered',
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('jobs.show', $job)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Lamaran Anda berhasil dikirim! Anda dapat memantau status lamaran di dashboard akun Anda.');
    }

    /**
     * Check application status page (for guests).
     */
    public function checkStatus()
    {
        return view('jobs.check_status');
    }

    /**
     * Search application status by email.
     */
    public function searchStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $applications = Application::where('applicant_email', $request->email)
            ->with(['jobListing', 'interviewSchedules'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('jobs.check_status', [
            'applications' => $applications,
            'searchEmail'  => $request->email,
        ]);
    }
}
