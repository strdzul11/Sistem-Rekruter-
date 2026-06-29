<?php

namespace App\Console\Commands;

use App\Models\JobListing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CloseExpiredJobListings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'jobs:close-expired';

    /**
     * The console command description.
     */
    protected $description = 'Tutup otomatis lowongan yang sudah melewati batas tanggal pendaftaran';

    public function handle(): int
    {
        $count = JobListing::where('status', 'active')
            ->whereNotNull('application_deadline')
            ->where('application_deadline', '<', now()->toDateString())
            ->update(['status' => 'closed']);

        $message = "jobs:close-expired — {$count} lowongan ditutup otomatis karena deadline sudah lewat.";

        Log::info($message);
        $this->info($message);

        return self::SUCCESS;
    }
}
