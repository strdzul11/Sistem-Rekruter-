<?php

namespace App\Http\Controllers;

use App\Models\InterviewSchedule;
use App\Services\InterviewSchedulingService;
use Illuminate\Http\Request;

class InterviewTokenController extends Controller
{
    public function __construct(private InterviewSchedulingService $schedulingService) {}

    /**
     * Kandidat quick-apply memilih slot interview via token.
     */
    public function select(Request $request, string $token)
    {
        $slot = InterviewSchedule::where('selection_token', $token)->first();

        if (!$slot) {
            return back()->with('error', 'Token jadwal interview tidak valid.');
        }

        try {
            $this->schedulingService->selectSlot($slot, $token);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Jadwal interview berhasil dikonfirmasi! Kami akan menghubungi Anda kembali untuk detail sesi wawancara.');
    }
}
