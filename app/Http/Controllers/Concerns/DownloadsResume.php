<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Services\PermissionChecker;

trait DownloadsResume
{
    /**
     * Download the resume of a given application securely.
     */
    public function downloadResume(Application $application)
    {
        PermissionChecker::denyUnless('applications.view');

        if (!$application->resume_path) {
            abort(404, 'File resume tidak ditemukan untuk lamaran ini.');
        }

        if (!Storage::disk('local')->exists($application->resume_path)) {
            abort(404, 'File resume fisik tidak ditemukan di penyimpanan server.');
        }

        $extension = pathinfo($application->resume_path, PATHINFO_EXTENSION);
        $safeName  = Str::slug($application->applicant_name ?? ($application->user?->name ?? 'Kandidat'));
        $filename  = "CV_{$safeName}.{$extension}";

        return Storage::disk('local')->download($application->resume_path, $filename);
    }
}
