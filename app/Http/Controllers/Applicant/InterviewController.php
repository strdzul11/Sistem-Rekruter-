<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Services\InterviewSchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterviewController extends Controller
{
    public function __construct(private InterviewSchedulingService $schedulingService) {}

    /**
     * Tampilkan slot interview yang ditawarkan ke kandidat yang login.
     */
    public function index()
    {
        $user = Auth::user();

        $proposedSlots = InterviewSchedule::with(['application.jobListing', 'interviewer'])
            ->whereHas('application', fn ($q) => $q->where('user_id', $user->id))
            ->where('is_proposed', true)
            ->where('status', 'scheduled')
            ->where('interview_date', '>', now())
            ->orderBy('interview_date')
            ->get();

        $confirmedSlots = InterviewSchedule::with(['application.jobListing', 'interviewer'])
            ->whereHas('application', fn ($q) => $q->where('user_id', $user->id))
            ->where('is_proposed', false)
            ->where('status', 'confirmed')
            ->orderBy('interview_date')
            ->get();

        return view('applicant.interviews', compact('proposedSlots', 'confirmedSlots'));
    }

    /**
     * Kandidat memilih satu slot interview.
     */
    public function select(Request $request, InterviewSchedule $slot)
    {
        // Pastikan slot ini milik aplikasi user yang sedang login
        $user = Auth::user();

        if ($slot->application->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak memilih jadwal ini.');
        }

        try {
            $this->schedulingService->selectSlot($slot);
        } catch (\RuntimeException $e) {
            return redirect()->route('applicant.interviews')->with('error', $e->getMessage());
        }

        return redirect()->route('applicant.interviews')
            ->with('success', 'Jadwal interview berhasil dipilih! Sampai jumpa di sesi interview Anda.');
    }
}
