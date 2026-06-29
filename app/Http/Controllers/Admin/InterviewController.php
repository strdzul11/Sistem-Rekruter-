<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InterviewSchedule;
use App\Models\InterviewQuestion;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InterviewController extends Controller
{
    public function index()
    {
        $interviews = InterviewSchedule::with(['application.user', 'application.jobListing', 'interviewer'])
            ->orderByDesc('interview_date')
            ->get();

        return view('admin.interviews.index', compact('interviews'));
    }

    /**
     * Form untuk membuat opsi jadwal interview (Admin).
     */
    public function create(Request $request)
    {
        $application = null;
        if ($request->filled('application_id')) {
            $application = Application::with(['user', 'jobListing'])->findOrFail($request->application_id);
        }

        $applications = Application::with(['user', 'jobListing'])
            ->whereIn('status', ['reviewed', 'interview_scheduled'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.interviews.create', compact('applications', 'application'));
    }

    /**
     * Simpan beberapa opsi jadwal interview sekaligus (Admin).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_id'   => 'required|exists:applications,id',
            'interview_type'   => 'required|in:phone,video,in-person,online',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'notes'            => 'nullable|string|max:1000',
            'slots'            => 'required|array|min:2',
            'slots.*.date'     => 'required|date|after:now',
            'slots.*.time'     => 'required|date_format:H:i',
        ]);

        $application = Application::findOrFail($validated['application_id']);
        $isQuickApply = $application->user_id === null;

        $createdSlots = 0;
        foreach ($validated['slots'] as $slot) {
            $interviewDate = $slot['date'] . ' ' . $slot['time'];

            InterviewSchedule::create([
                'application_id'   => $application->id,
                'interviewer_id'   => Auth::id(),
                'interview_type'   => $validated['interview_type'],
                'interview_date'   => $interviewDate,
                'duration_minutes' => $validated['duration_minutes'],
                'status'           => 'scheduled',
                'notes'            => $validated['notes'] ?? null,
                'is_proposed'      => true,
                'selection_token'  => $isQuickApply ? Str::random(40) : null,
                'meeting_link'     => in_array($validated['interview_type'], ['video', 'online'])
                    ? 'https://meet.google.com/interview-' . Str::random(10)
                    : null,
            ]);

            $createdSlots++;
        }

        // Update interview_status di application
        $application->update([
            'interview_status' => 'scheduled',
            'status'           => 'interview_scheduled'
        ]);

        AuditLogger::log('create', 'interview_schedules', "{$createdSlots} opsi jadwal dibuat untuk lamaran #{$application->id}", Application::class, $application->id);

        return redirect()->route('admin.interviews.index')
            ->with('success', "{$createdSlots} opsi jadwal interview berhasil dibuat. Kandidat dapat memilih slot yang sesuai.");
    }

    /**
     * Tampilkan detail interview (Admin).
     */
    public function show(InterviewSchedule $interview)
    {
        $interview->load(['application.user', 'application.jobListing', 'interviewer']);

        $position = $interview->application->jobListing->position;

        $questions = InterviewQuestion::where('is_active', true)
            ->where(function ($query) use ($position) {
                $query->whereNull('job_position')
                      ->orWhere('job_position', $position);
            })
            ->get()
            ->groupBy('question_type');

        return view('admin.interviews.show', compact('interview', 'questions'));
    }
}
