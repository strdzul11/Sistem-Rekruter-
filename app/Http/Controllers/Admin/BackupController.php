<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index()
    {
        $backups = DatabaseBackupService::list();

        return view('admin.backups.index', compact('backups'));
    }

    public function store()
    {
        $filename = DatabaseBackupService::create();

        AuditLogger::log('create', 'backup', "Backup database dibuat: {$filename}");

        return back()->with('success', "Backup berhasil dibuat: {$filename}");
    }

    public function restore(Request $request)
    {
        $request->validate(['filename' => 'required|string']);

        try {
            DatabaseBackupService::restore($request->filename);
            AuditLogger::log('restore', 'backup', "Restore database dari: {$request->filename}");

            return back()->with('success', 'Database berhasil dipulihkan dari backup.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Restore gagal: ' . $e->getMessage());
        }
    }

    public function destroy(string $filename)
    {
        DatabaseBackupService::delete($filename);
        AuditLogger::log('delete', 'backup', "Backup dihapus: {$filename}");

        return back()->with('success', 'File backup berhasil dihapus.');
    }
}
