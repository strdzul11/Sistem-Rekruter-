<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MoveResumesToPrivateDisk extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:move-resumes-private';

    /**
     * The console command description.
     */
    protected $description = 'Pindahkan file resume dari disk public ke disk local untuk alasan keamanan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $applications = Application::whereNotNull('resume_path')->get();
        $this->info("Menemukan " . $applications->count() . " lamaran dengan resume.");

        $successCount = 0;
        $failCount = 0;

        foreach ($applications as $app) {
            $path = $app->resume_path;

            // Pastikan file ada di disk public
            if (Storage::disk('public')->exists($path)) {
                try {
                    // Ambil konten file dari public
                    $content = Storage::disk('public')->get($path);

                    // Simpan di local
                    Storage::disk('local')->put($path, $content);

                    // Verifikasi file sudah tersalin ke local
                    if (Storage::disk('local')->exists($path)) {
                        // Hapus file lama di public
                        Storage::disk('public')->delete($path);
                        $successCount++;
                        $this->line("Berhasil memindahkan: {$path}");
                    } else {
                        $this->error("Gagal verifikasi penyimpanan local untuk: {$path}");
                        $failCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("Error memindahkan {$path}: " . $e->getMessage());
                    $failCount++;
                }
            } else {
                // Mungkin sudah di disk local, atau memang file fisiknya hilang
                if (Storage::disk('local')->exists($path)) {
                    $this->line("File sudah ada di disk local: {$path}");
                } else {
                    $this->warn("File tidak ditemukan di disk public maupun local: {$path}");
                }
            }
        }

        $this->info("Proses selesai. Sukses dipindahkan: {$successCount}, Gagal: {$failCount}.");
    }
}
