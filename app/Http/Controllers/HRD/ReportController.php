<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobListing;
use App\Services\PermissionChecker;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        PermissionChecker::denyUnless('reports.view');

        // 1. Tren aplikasi 6 bulan terakhir
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $label = $month->translatedFormat('F Y');
            $count = Application::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $trends[$label] = $count;
        }

        // 2. Distribusi status
        $statusCounts = Application::select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = ['pending', 'reviewed', 'interview_scheduled', 'accepted', 'rejected'];
        $distribution = [];
        foreach ($statuses as $status) {
            $distribution[$status] = $statusCounts[$status] ?? 0;
        }

        // 3. Top 5 lowongan paling banyak dilamar
        $topJobs = JobListing::withCount('applications')
            ->orderByDesc('applications_count')
            ->limit(5)
            ->get();

        // 4. 10 aplikasi terbaru
        $latestApplications = Application::with(['user', 'jobListing'])
            ->latest()
            ->limit(10)
            ->get();

        // 5. 4 angka ringkasan
        $summary = [
            'total_applications' => Application::count(),
            'pending_review'     => Application::where('status', 'pending')->count(),
            'accepted'           => Application::where('status', 'accepted')->count(),
            'active_jobs'        => JobListing::where('status', 'active')->count(),
        ];

        return view('hrd.reports.index', compact('trends', 'distribution', 'topJobs', 'latestApplications', 'summary'));
    }
}
