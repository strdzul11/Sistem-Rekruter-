<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseBackupService
{
    private const BACKUP_TABLES = [
        'users',
        'job_listings',
        'applications',
        'applicant_profiles',
        'evaluation_criteria',
        'application_evaluations',
        'application_rankings',
        'interview_questions',
        'interview_schedules',
        'interview_responses',
        'interview_feedback',
        'calendar_integrations',
        'system_settings',
        'role_permissions',
        'audit_logs',
    ];

    public static function backupDirectory(): string
    {
        $dir = storage_path('app/backups');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    public static function create(): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_{$timestamp}.json";
        $path = self::backupDirectory() . DIRECTORY_SEPARATOR . $filename;

        $payload = [
            'created_at' => now()->toIso8601String(),
            'app' => config('app.name'),
            'tables' => [],
        ];

        foreach (self::BACKUP_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $payload['tables'][$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        }

        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $filename;
    }

    public static function list(): array
    {
        $files = File::glob(self::backupDirectory() . DIRECTORY_SEPARATOR . 'backup_*.json') ?: [];

        return collect($files)
            ->map(fn ($path) => [
                'filename' => basename($path),
                'size' => File::size($path),
                'created_at' => date('Y-m-d H:i:s', File::lastModified($path)),
            ])
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public static function restore(string $filename): void
    {
        $path = self::backupDirectory() . DIRECTORY_SEPARATOR . basename($filename);

        if (! File::exists($path)) {
            throw new \RuntimeException('File backup tidak ditemukan.');
        }

        $payload = json_decode(File::get($path), true);

        if (! isset($payload['tables']) || ! is_array($payload['tables'])) {
            throw new \RuntimeException('Format backup tidak valid.');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (array_reverse(self::BACKUP_TABLES) as $table) {
            if (! isset($payload['tables'][$table]) || ! Schema::hasTable($table)) {
                continue;
            }
            DB::table($table)->truncate();
            $rows = $payload['tables'][$table];
            if (! empty($rows)) {
                foreach (array_chunk($rows, 100) as $chunk) {
                    DB::table($table)->insert($chunk);
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public static function delete(string $filename): void
    {
        $path = self::backupDirectory() . DIRECTORY_SEPARATOR . basename($filename);
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
