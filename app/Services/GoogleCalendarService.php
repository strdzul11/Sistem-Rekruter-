<?php

namespace App\Services;

use App\Models\CalendarIntegration;
use App\Models\InterviewSchedule;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event as GoogleCalendarEvent;
use Google\Service\Calendar\EventDateTime as GoogleEventDateTime;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private function getGoogleClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google_calendar.client_id'));
        $client->setClientSecret(config('services.google_calendar.client_secret'));
        $client->setRedirectUri(config('services.google_calendar.redirect_uri'));
        $client->addScope(GoogleCalendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        return $client;
    }

    public function getAuthUrl(int $userId): string
    {
        $client = $this->getGoogleClient();
        // State terenkripsi untuk keamanan
        $state = Crypt::encryptString(json_encode([
            'user_id'   => $userId,
            'timestamp' => time(),
        ]));
        $client->setState($state);
        return $client->createAuthUrl();
    }

    public function handleCallback(string $code, string $state): bool
    {
        try {
            $decrypted = json_decode(Crypt::decryptString($state), true);
            $userId    = $decrypted['user_id'];

            $client = $this->getGoogleClient();
            $token  = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                Log::error('Google Calendar OAuth error: ' . $token['error_description']);
                return false;
            }

            CalendarIntegration::updateOrCreate(
                [
                    'user_id'          => $userId,
                    'integration_type' => 'google',
                ],
                [
                    'access_token'  => $token['access_token'],
                    'refresh_token' => $token['refresh_token'] ?? null,
                    'is_active'     => true,
                    'expires_at'    => now()->addSeconds($token['expires_in']),
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Google Calendar callback failure: ' . $e->getMessage());
            return false;
        }
    }

    private function getAuthenticatedClient(CalendarIntegration $integration): ?GoogleClient
    {
        if (!$integration->is_active) {
            return null;
        }

        $client = $this->getGoogleClient();
        $client->setAccessToken([
            'access_token'  => $integration->access_token,
            'refresh_token' => $integration->refresh_token,
            'expires_in'    => now()->diffInSeconds($integration->expires_at, false),
        ]);

        if ($client->isAccessTokenExpired()) {
            if (!$integration->refresh_token) {
                Log::warning("Refresh token not found for integration ID {$integration->id}");
                return null;
            }
            try {
                $newToken = $client->fetchAccessTokenWithRefreshToken($integration->refresh_token);
                if (isset($newToken['error'])) {
                    Log::error("Failed to refresh Google token: " . $newToken['error_description']);
                    return null;
                }

                $integration->update([
                    'access_token' => $newToken['access_token'],
                    'expires_at'   => now()->addSeconds($newToken['expires_in']),
                ]);
            } catch (\Exception $e) {
                Log::error("Error refreshing token: " . $e->getMessage());
                return null;
            }
        }

        return $client;
    }

    public function createEvent(InterviewSchedule $interview): ?string
    {
        try {
            $integration = CalendarIntegration::where('user_id', $interview->interviewer_id)
                ->where('integration_type', 'google')
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return null;
            }

            $client = $this->getAuthenticatedClient($integration);
            if (!$client) {
                return null;
            }

            $service = new GoogleCalendar($client);

            $applicantName = $interview->application->applicant_name ?? ($interview->application->user?->name ?? 'Pelamar');
            $position      = $interview->application->jobListing->position;

            $description = "Interview untuk posisi: {$position}\n" .
                           "Pelamar: {$applicantName}\n" .
                           "Email Pelamar: " . ($interview->application->applicant_email ?? $interview->application->user?->email) . "\n" .
                           "Telepon Pelamar: " . ($interview->application->applicant_phone ?? '-') . "\n\n" .
                           "Catatan: " . ($interview->notes ?? 'Tidak ada');

            $startDateTime = $interview->interview_date->format(\DateTime::RFC3339);
            $endDateTime   = $interview->interview_date->copy()->addMinutes($interview->duration_minutes)->format(\DateTime::RFC3339);

            $event = new GoogleCalendarEvent([
                'summary'     => "Interview: {$applicantName} - {$position}",
                'description' => $description,
                'location'    => $interview->meeting_link ?? ($interview->meeting_room ?? 'Online / Video Call'),
                'start'       => new GoogleEventDateTime([
                    'dateTime' => $startDateTime,
                    'timeZone' => $interview->timezone ?? 'Asia/Jakarta',
                ]),
                'end'         => new GoogleEventDateTime([
                    'dateTime' => $endDateTime,
                    'timeZone' => $interview->timezone ?? 'Asia/Jakarta',
                ]),
            ]);

            $calendarId   = $integration->calendar_id ?? 'primary';
            $createdEvent = $service->events->insert($calendarId, $event);

            return $createdEvent->getId();
        } catch (\Exception $e) {
            Log::warning('Gagal membuat event Google Calendar: ' . $e->getMessage());
            return null;
        }
    }

    public function updateEvent(InterviewSchedule $interview): void
    {
        if (!$interview->google_calendar_event_id) {
            return;
        }

        try {
            $integration = CalendarIntegration::where('user_id', $interview->interviewer_id)
                ->where('integration_type', 'google')
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return;
            }

            $client = $this->getAuthenticatedClient($integration);
            if (!$client) {
                return;
            }

            $service = new GoogleCalendar($client);

            $applicantName = $interview->application->applicant_name ?? ($interview->application->user?->name ?? 'Pelamar');
            $position      = $interview->application->jobListing->position;

            $description = "Interview untuk posisi: {$position}\n" .
                           "Pelamar: {$applicantName}\n" .
                           "Email Pelamar: " . ($interview->application->applicant_email ?? $interview->application->user?->email) . "\n" .
                           "Telepon Pelamar: " . ($interview->application->applicant_phone ?? '-') . "\n\n" .
                           "Catatan: " . ($interview->notes ?? 'Tidak ada');

            $startDateTime = $interview->interview_date->format(\DateTime::RFC3339);
            $endDateTime   = $interview->interview_date->copy()->addMinutes($interview->duration_minutes)->format(\DateTime::RFC3339);

            $event = new GoogleCalendarEvent([
                'summary'     => "Interview: {$applicantName} - {$position}",
                'description' => $description,
                'location'    => $interview->meeting_link ?? ($interview->meeting_room ?? 'Online / Video Call'),
                'start'       => new GoogleEventDateTime([
                    'dateTime' => $startDateTime,
                    'timeZone' => $interview->timezone ?? 'Asia/Jakarta',
                ]),
                'end'         => new GoogleEventDateTime([
                    'dateTime' => $endDateTime,
                    'timeZone' => $interview->timezone ?? 'Asia/Jakarta',
                ]),
            ]);

            $calendarId = $integration->calendar_id ?? 'primary';
            $service->events->update($calendarId, $interview->google_calendar_event_id, $event);
        } catch (\Exception $e) {
            Log::warning('Gagal memperbarui event Google Calendar: ' . $e->getMessage());
        }
    }

    public function deleteEvent(InterviewSchedule $interview): void
    {
        if (!$interview->google_calendar_event_id) {
            return;
        }

        try {
            $integration = CalendarIntegration::where('user_id', $interview->interviewer_id)
                ->where('integration_type', 'google')
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return;
            }

            $client = $this->getAuthenticatedClient($integration);
            if (!$client) {
                return;
            }

            $service    = new GoogleCalendar($client);
            $calendarId = $integration->calendar_id ?? 'primary';
            $service->events->delete($calendarId, $interview->google_calendar_event_id);
        } catch (\Exception $e) {
            Log::warning('Gagal menghapus event Google Calendar: ' . $e->getMessage());
        }
    }
}
