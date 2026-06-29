<?php

namespace App\Services;

use App\Models\Application;
use App\Models\LetterTemplate;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class LetterGenerator
{
    /**
     * Generate surat PDF untuk lamaran tertentu.
     *
     * @param  Application $application
     * @param  string      $type         'acceptance' | 'rejection' | 'interview_invitation'
     * @return string                    Path file PDF yang disimpan
     * @throws \RuntimeException
     */
    public function generate(Application $application, string $type): string
    {
        // Cari template aktif
        $template = LetterTemplate::where('type', $type)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            throw new \RuntimeException("Template surat untuk tipe '{$type}' yang aktif tidak ditemukan.");
        }

        // Ambil data untuk placeholder
        $applicantName = $application->applicant_name ?? ($application->user->name ?? 'Kandidat');
        $position      = $application->jobListing->position ?? 'Posisi';
        $companyName   = SystemSetting::getVal('company_name', 'PT. PUTRI KEBUN LESTARI');
        $dateStr       = now()->translatedFormat('d F Y');

        // Ganti placeholder
        $subject = str_replace(
            ['{{applicant_name}}', '{{position}}', '{{company_name}}', '{{date}}'],
            [$applicantName, $position, $companyName, $dateStr],
            $template->subject
        );

        $body = str_replace(
            ['{{applicant_name}}', '{{position}}', '{{company_name}}', '{{date}}'],
            [$applicantName, $position, $companyName, $dateStr],
            $template->body
        );

        // Buat HTML sederhana dengan styling dasar dompdf
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>{$subject}</title>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333; }
                .header { margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .company-name { font-size: 20px; font-weight: bold; text-transform: uppercase; }
                .date { text-align: right; margin-bottom: 20px; }
                .subject { font-weight: bold; margin-bottom: 30px; text-decoration: underline; }
                .content { margin-bottom: 40px; text-align: justify; }
                .signature { float: right; width: 200px; margin-top: 40px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='company-name'>{$companyName}</div>
                <div style='font-size: 11px; color: #666;'>Sistem Rekrutmen Otomatis</div>
            </div>
            <div class='date'>Jakarta, {$dateStr}</div>
            <div class='content'>
                {$body}
            </div>
            <div class='signature'>
                <p>Hormat Kami,</p>
                <p style='margin-top: 60px;'><strong>Tim HRD {$companyName}</strong></p>
            </div>
        </body>
        </html>
        ";

        // Pastikan direktori tujuan ada
        Storage::makeDirectory('letters');

        // Buat nama file unik
        $timestamp = time();
        $filename  = "letters/{$application->id}_{$type}_{$timestamp}.pdf";
        $absolutePath = storage_path("app/{$filename}");

        // Load HTML & render PDF
        Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->save($absolutePath);

        return $filename;
    }
}
