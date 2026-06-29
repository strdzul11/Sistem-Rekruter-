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
}
