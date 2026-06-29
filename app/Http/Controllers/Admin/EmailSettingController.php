<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AuditLogger;
use App\Services\MailConfigurator;
use Illuminate\Http\Request;

class EmailSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::pluck('value', 'key')->all();

        return view('admin.email_settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mail_enabled' => 'nullable|boolean',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl,',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
        ]);

        SystemSetting::setVal('mail_enabled', $request->boolean('mail_enabled') ? '1' : '0');
        SystemSetting::setVal('smtp_host', $request->smtp_host ?? '');
        SystemSetting::setVal('smtp_port', $request->smtp_port ?? '587');
        SystemSetting::setVal('smtp_username', $request->smtp_username ?? '');

        if ($request->filled('smtp_password')) {
            SystemSetting::setVal('smtp_password', $request->smtp_password);
        }

        SystemSetting::setVal('smtp_encryption', $request->smtp_encryption ?? 'tls');
        SystemSetting::setVal('smtp_from_address', $request->smtp_from_address ?? '');
        SystemSetting::setVal('smtp_from_name', $request->smtp_from_name ?? '');

        AuditLogger::log('update', 'email_settings', 'Konfigurasi SMTP diperbarui');

        return back()->with('success', 'Pengaturan email berhasil disimpan.');
    }

    public function test(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);

        try {
            MailConfigurator::sendTest($request->test_email);
            AuditLogger::log('test', 'email_settings', "Email test dikirim ke {$request->test_email}");

            return back()->with('success', 'Email test berhasil dikirim ke ' . $request->test_email);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }
}
