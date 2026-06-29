<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [JobController::class, 'landing'])->name('landing');
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
Route::post('/jobs/{job}/quick-apply', [JobController::class, 'quickApply'])->name('jobs.quickApply');
Route::get('/check-status', [JobController::class, 'checkStatus'])->name('jobs.checkStatus');
Route::post('/check-status', [JobController::class, 'searchStatus'])->name('jobs.searchStatus');

// Fitur #1: Pilih slot interview lewat token (quick-apply tanpa login)
Route::post('/interviews/select/{token}', [\App\Http\Controllers\InterviewTokenController::class, 'select'])
    ->name('interviews.selectByToken');

// Public callback untuk Google Calendar OAuth (tanpa middleware auth)
Route::get('/calendar/google/callback', [\App\Http\Controllers\CalendarIntegrationController::class, 'handleGoogleCallback'])
    ->name('calendar.google.callback');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Google Calendar connect & disconnect
    Route::get('/calendar/connect/google', [\App\Http\Controllers\CalendarIntegrationController::class, 'redirectToGoogle'])
        ->name('calendar.connect.google');
    Route::post('/calendar/disconnect', [\App\Http\Controllers\CalendarIntegrationController::class, 'disconnect'])
        ->name('calendar.disconnect');

    // Apply as logged-in user
    Route::post('/jobs/{job}/apply', [JobController::class, 'apply'])->name('jobs.apply');

    // Admin Group — konfigurasi sistem
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('criteria', \App\Http\Controllers\Admin\CriteriaController::class)->except(['show', 'create', 'edit']);
        Route::resource('questions', \App\Http\Controllers\Admin\QuestionController::class);
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit_logs.index');
        Route::get('/backups', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [\App\Http\Controllers\Admin\BackupController::class, 'store'])->name('backups.store');
        Route::post('/backups/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/backups/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('backups.destroy');
        Route::get('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('permissions.index');
        Route::put('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'update'])->name('permissions.update');
        Route::get('/email-settings', [\App\Http\Controllers\Admin\EmailSettingController::class, 'index'])->name('email_settings.index');
        Route::post('/email-settings', [\App\Http\Controllers\Admin\EmailSettingController::class, 'update'])->name('email_settings.update');
        Route::post('/email-settings/test', [\App\Http\Controllers\Admin\EmailSettingController::class, 'test'])->name('email_settings.test');

        // Admin secure applications & downloads
        Route::get('/applications/{application}/resume', [\App\Http\Controllers\Admin\ApplicationController::class, 'downloadResume'])
            ->name('applications.resume');
        Route::get('/applications/{application}/preview', [\App\Http\Controllers\Admin\ApplicationController::class, 'preview'])
            ->name('applications.preview');
        Route::resource('applications', \App\Http\Controllers\Admin\ApplicationController::class)->only(['index', 'show']);

        // Evaluations (admin bisa beri penilaian)
        Route::get('/evaluations/{application}/preview', [\App\Http\Controllers\Admin\EvaluationController::class, 'preview'])
            ->name('evaluations.preview');
        Route::get('/evaluations', [\App\Http\Controllers\Admin\EvaluationController::class, 'index'])->name('evaluations.index');
        Route::get('/evaluations/{evaluation}', [\App\Http\Controllers\Admin\EvaluationController::class, 'show'])->name('evaluations.show');
        Route::post('/evaluations', [\App\Http\Controllers\Admin\EvaluationController::class, 'store'])->name('evaluations.store');

        // Fitur #5: Template surat (admin kelola)
        Route::resource('letter-templates', \App\Http\Controllers\Admin\LetterTemplateController::class);

        // Admin Interviews CRUD
        Route::resource('interviews', \App\Http\Controllers\Admin\InterviewController::class)->only(['index', 'create', 'store', 'show']);
    });

    // HRD Group — operasional rekrutmen
    Route::middleware('role:hrd')->prefix('hrd')->name('hrd.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\HRD\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('jobs', \App\Http\Controllers\HRD\JobController::class)->except(['show']);

        // Fitur #2: Bulk action (harus di atas resource agar tidak ditangkap sebagai {application} id)
        Route::post('/applications/bulk-update', [\App\Http\Controllers\HRD\ApplicationController::class, 'bulkUpdate'])
            ->name('applications.bulkUpdate');

        // HRD secure resume, preview, & applications
        Route::get('/applications/{application}/resume', [\App\Http\Controllers\HRD\ApplicationController::class, 'downloadResume'])
            ->name('applications.resume');
        Route::get('/applications/{application}/preview', [\App\Http\Controllers\HRD\ApplicationController::class, 'preview'])
            ->name('applications.preview');
        Route::resource('applications', \App\Http\Controllers\HRD\ApplicationController::class)->only(['index', 'show', 'update']);

        // Fitur #5: Download surat PDF
        Route::get('/applications/{application}/letter', [\App\Http\Controllers\HRD\ApplicationController::class, 'downloadLetter'])
            ->name('applications.downloadLetter');

        // HRD evaluations & preview
        Route::get('/evaluations/{application}/preview', [\App\Http\Controllers\HRD\EvaluationController::class, 'preview'])
            ->name('evaluations.preview');
        Route::get('/evaluations', [\App\Http\Controllers\HRD\EvaluationController::class, 'index'])->name('evaluations.index');
        Route::get('/evaluations/{application}', [\App\Http\Controllers\HRD\EvaluationController::class, 'show'])->name('evaluations.show');
        Route::post('/evaluations', [\App\Http\Controllers\HRD\EvaluationController::class, 'store'])->name('evaluations.store');

        Route::get('/rankings', [\App\Http\Controllers\HRD\RankingController::class, 'index'])->name('rankings.index');

        // HRD reports index
        Route::get('/reports', [\App\Http\Controllers\HRD\ReportController::class, 'index'])->name('reports.index');

        // HRD self-schedule interview & link regeneration
        Route::get('/interviews', [\App\Http\Controllers\HRD\InterviewController::class, 'index'])->name('interviews.index');
        Route::get('/interviews/create', [\App\Http\Controllers\HRD\InterviewController::class, 'create'])->name('interviews.create');
        Route::post('/interviews', [\App\Http\Controllers\HRD\InterviewController::class, 'store'])->name('interviews.store');
        Route::get('/interviews/{interview}', [\App\Http\Controllers\HRD\InterviewController::class, 'show'])->name('interviews.show');
        Route::post('/interviews/{interview}/regenerate-link', [\App\Http\Controllers\HRD\InterviewController::class, 'regenerateLink'])
            ->name('interviews.regenerateLink');
    });

    // Applicant Group
    Route::middleware('role:applicant')->prefix('applicant')->name('applicant.')->group(function () {
        Route::get('/dashboard', function () { return view('applicant.dashboard'); })->name('dashboard');
        Route::get('/profile', function () { return view('applicant.profile'); })->name('profile');
        // Fitur #1: Applicant pilih slot interview
        Route::get('/interviews', [\App\Http\Controllers\Applicant\InterviewController::class, 'index'])->name('interviews');
        Route::post('/interviews/{slot}/select', [\App\Http\Controllers\Applicant\InterviewController::class, 'select'])->name('interviews.select');
        Route::get('/evaluation-results', function () { return view('applicant.evaluation_results'); })->name('evaluation_results');
    });
});

require __DIR__.'/auth.php';
