<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Tren aplikasi 12 bulan terakhir
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $label = $month->translatedFormat('F Y');
            $count = Application::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $trends[$label] = $count;
        }

        // 2. Aplikasi per lowongan (semua lowongan)
        $jobsReport = JobListing::withCount('applications')
            ->orderByDesc('applications_count')
            ->get();

        // 3. Distribusi status
        $statusCounts = Application::select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = ['pending', 'reviewed', 'interview_scheduled', 'accepted', 'rejected'];
        $distribution = [];
        foreach ($statuses as $status) {
            $distribution[$status] = $statusCounts[$status] ?? 0;
        }

        // 4. 4 angka ringkasan
        $summary = [
            'total_jobs'        => JobListing::count(),
            'active_jobs'       => JobListing::where('status', 'active')->count(),
            'total_applications'=> Application::count(),
            'registered_users'  => User::where('role', 'applicant')->count(),
        ];

        return view('admin.reports.index', compact('trends', 'jobsReport', 'distribution', 'summary'));
    }

    public function print()
    {
        // 1. Tren aplikasi 12 bulan terakhir
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $label = $month->translatedFormat('M Y');
            $count = Application::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $trends[$label] = $count;
        }

        // 2. Laporan per lowongan
        $jobsReport = JobListing::withCount('applications')
            ->orderByDesc('applications_count')
            ->get();

        // 3. Distribusi status
        $statusCounts = Application::select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = ['pending', 'reviewed', 'interview_scheduled', 'accepted', 'rejected'];
        $statusLabels = [
            'pending'             => 'Menunggu',
            'reviewed'            => 'Ditinjau',
            'interview_scheduled' => 'Interview',
            'accepted'            => 'Diterima',
            'rejected'            => 'Ditolak',
        ];
        $distribution = [];
        foreach ($statuses as $status) {
            $distribution[$status] = [
                'count' => $statusCounts[$status] ?? 0,
                'label' => $statusLabels[$status],
            ];
        }

        // 4. Ringkasan statistik
        $summary = [
            'total_jobs'         => JobListing::count(),
            'active_jobs'        => JobListing::where('status', 'active')->count(),
            'inactive_jobs'      => JobListing::where('status', '!=', 'active')->count(),
            'total_applications' => Application::count(),
            'registered_users'   => User::where('role', 'applicant')->count(),
            'hrd_users'          => User::where('role', 'hrd')->count(),
            'pending_apps'       => Application::where('status', 'pending')->count(),
            'accepted_apps'      => Application::where('status', 'accepted')->count(),
            'rejected_apps'      => Application::where('status', 'rejected')->count(),
        ];

        // 5. Lamaran terbaru (10 data)
        $recentApplications = Application::with(['jobListing', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        // 6. Top 5 lowongan terpopuler
        $topJobs = JobListing::withCount('applications')
            ->orderByDesc('applications_count')
            ->limit(5)
            ->get();

        $generatedAt = Carbon::now()->translatedFormat('d F Y, H:i');

        return view('admin.reports.print', compact(
            'trends', 'jobsReport', 'distribution', 'summary',
            'recentApplications', 'topJobs', 'generatedAt'
        ));
    }
}
