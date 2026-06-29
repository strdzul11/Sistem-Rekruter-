<?php

namespace App\Services;

use App\Models\Application;
use App\Models\InterviewSchedule;
use Illuminate\Support\Facades\DB;

class InterviewSchedulingService
{
    /**
     * Kandidat memilih slot interview.
     *
     * @param  InterviewSchedule $slot   Slot yang dipilih
     * @param  string|null       $token  Token untuk quick-apply (validasi tambahan)
     * @throws \RuntimeException
     */
    public function selectSlot(InterviewSchedule $slot, ?string $token = null): void
    {
        // Validasi token untuk kandidat quick-apply
        if ($token !== null && $slot->selection_token !== $token) {
            throw new \RuntimeException('Token tidak valid.');
        }

        // Validasi slot masih proposed
        if (!$slot->is_proposed) {
            throw new \RuntimeException('Slot jadwal ini sudah tidak tersedia.');
        }

        // Validasi tanggal interview belum lewat
        if ($slot->interview_date->isPast()) {
            throw new \RuntimeException('Jadwal interview ini sudah lewat. Silakan pilih slot lain.');
        }

        DB::transaction(function () use ($slot) {
            // Slot dipilih: ubah status
            $slot->update([
                'is_proposed' => false,
                'status'      => 'confirmed',
            ]);

            // Batalkan slot lain untuk application yang sama (jangan hapus, untuk audit)
            InterviewSchedule::where('application_id', $slot->application_id)
                ->where('id', '!=', $slot->id)
                ->where('is_proposed', true)
                ->update(['status' => 'cancelled']);

            // Update status application
            Application::where('id', $slot->application_id)
                ->update(['status' => 'interview_scheduled']);
        });

        // Sinkronisasi Google Calendar setelah konfirmasi berhasil
        try {
            $calendarService = app(\App\Services\GoogleCalendarService::class);
            $eventId = $calendarService->createEvent($slot->fresh());
            if ($eventId) {
                $slot->update(['google_calendar_event_id' => $eventId]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal sinkronisasi Google Calendar saat konfirmasi slot: ' . $e->getMessage());
        }
    }
}
