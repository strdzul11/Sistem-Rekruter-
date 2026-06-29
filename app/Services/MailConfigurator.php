<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailConfigurator
{
    public static function applyFromSettings(): void
    {
        if (! SystemSetting::getVal('mail_enabled')) {
            return;
        }

        $host = SystemSetting::getVal('smtp_host');
        if (! $host) {
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', (int) SystemSetting::getVal('smtp_port', 587));
        Config::set('mail.mailers.smtp.username', SystemSetting::getVal('smtp_username'));
        Config::set('mail.mailers.smtp.password', SystemSetting::getVal('smtp_password'));
        Config::set('mail.mailers.smtp.encryption', SystemSetting::getVal('smtp_encryption', 'tls') ?: null);

        $fromAddress = SystemSetting::getVal('smtp_from_address');
        $fromName = SystemSetting::getVal('smtp_from_name');

        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName ?: config('app.name'));
        }
    }

    public static function sendTest(string $toEmail): void
    {
        self::applyFromSettings();

        Mail::raw(
            'Email test dari sistem rekruter ' . config('app.name') . '. Konfigurasi SMTP berhasil.',
            fn ($message) => $message->to($toEmail)->subject('Test Email SMTP - Rekruter')
        );
    }
}
