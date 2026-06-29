<?php

namespace App\Http\Controllers;

use App\Models\CalendarIntegration;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarIntegrationController extends Controller
{
    public function __construct(private GoogleCalendarService $calendarService) {}

    public function redirectToGoogle()
    {
        $url = $this->calendarService->getAuthUrl(Auth::id());
        return redirect()->away($url);
    }

    public function handleGoogleCallback(Request $request)
    {
        $code  = $request->query('code');
        $state = $request->query('state');

        if (!$code || !$state) {
            return redirect()->route('profile.edit')
                ->with('error', 'Otorisasi Google Calendar dibatalkan atau tidak valid.');
        }

        $success = $this->calendarService->handleCallback($code, $state);

        if ($success) {
            return redirect()->route('profile.edit')
                ->with('success', 'Google Calendar berhasil terhubung!');
        }

        return redirect()->route('profile.edit')
            ->with('error', 'Gagal menghubungkan Google Calendar.');
    }

    public function disconnect()
    {
        CalendarIntegration::where('user_id', Auth::id())
            ->where('integration_type', 'google')
            ->update(['is_active' => false]);

        return redirect()->route('profile.edit')
            ->with('success', 'Google Calendar berhasil diputus.');
    }
}
