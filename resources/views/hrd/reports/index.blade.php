<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Analitik Rekrutmen (HRD)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Header Actions --}}
            <div class="flex justify-between items-center mb-4 print:hidden">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Analitik & Statistik Rekrutmen</h3>
                    <p class="text-sm text-gray-500">Visualisasi data lamaran masuk dan aktivitas rekrutmen.</p>
                </div>
                <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-600 mr-4"><i class="fas fa-file-alt text-xl"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase">Total Lamaran</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $summary['total_applications'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-xl bg-amber-50 text-amber-600 mr-4"><i class="fas fa-clock text-xl"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase">Menunggu Review</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $summary['pending_review'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-xl bg-green-50 text-green-600 mr-4"><i class="fas fa-check-circle text-xl"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase">Diterima</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $summary['accepted'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-xl bg-purple-50 text-purple-600 mr-4"><i class="fas fa-briefcase text-xl"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase">Lowongan Aktif</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $summary['active_jobs'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Charts Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Line Chart --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
                    <h4 class="font-bold text-gray-900 text-sm mb-4"><i class="fas fa-chart-line mr-2 text-emerald-500"></i>Tren Lamaran Bulanan (6 Bulan Terakhir)</h4>
                    <div class="relative h-72">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>

                {{-- Doughnut Chart --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="font-bold text-gray-900 text-sm mb-4"><i class="fas fa-chart-pie mr-2 text-emerald-500"></i>Distribusi Status Lamaran</h4>
                    <div class="relative h-72">
                        <canvas id="statusDistributionChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Lists --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Top Jobs --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="font-bold text-gray-900 text-sm mb-4"><i class="fas fa-trophy mr-2 text-yellow-500"></i>Top 5 Lowongan Terpopuler</h4>
                    <div class="space-y-3">
                        @forelse($topJobs as $index => $job)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <div>
                                    <div class="font-bold text-gray-900 text-sm">#{{ $index + 1 }} {{ $job->position }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $job->company }}</div>
                                </div>
                                <span class="bg-emerald-50 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-bold">
                                    {{ $job->applications_count }} Lamaran
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-400 text-sm text-center py-6">Belum ada data pelamar.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Latest Applications --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
                    <h4 class="font-bold text-gray-900 text-sm mb-4"><i class="fas fa-history mr-2 text-blue-500"></i>10 Lamaran Terbaru</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider">
                                    <th class="pb-3 px-2">Pelamar</th>
                                    <th class="pb-3 px-2">Lowongan</th>
                                    <th class="pb-3 px-2">Status</th>
                                    <th class="pb-3 px-2 text-right">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium">
                                @forelse($latestApplications as $app)
                                    <tr>
                                        <td class="py-3 px-2">
                                            <div class="font-bold text-gray-900">{{ $app->applicant_name ?? ($app->user?->name ?? '-') }}</div>
                                            <div class="text-xxs text-gray-400 font-normal mt-0.5">{{ $app->applicant_email ?? ($app->user?->email ?? '-') }}</div>
                                        </td>
                                        <td class="py-3 px-2 text-gray-700">{{ $app->jobListing->position }}</td>
                                        <td class="py-3 px-2">
                                            <span class="px-2 py-0.5 rounded-full text-xxs font-semibold uppercase {{ $app->status === 'accepted' ? 'bg-green-50 text-green-700' : ($app->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700') }}">
                                                {{ $app->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-right text-gray-450">{{ $app->created_at ? $app->created_at->format('d/m/Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-6 text-gray-400">Belum ada lamaran masuk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Chart.js --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Data Line Chart Tren Bulanan
            const trendData = @js($trends);
            const trendLabels = Object.keys(trendData);
            const trendValues = Object.values(trendData);

            new window.Chart(document.getElementById('monthlyTrendChart'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Jumlah Lamaran',
                        data: trendValues,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });

            // Data Doughnut Chart Distribusi Status
            const distData = @js($distribution);
            const distLabels = ['Pending', 'Reviewed', 'Interview Scheduled', 'Accepted', 'Rejected'];
            const distValues = [
                distData.pending || 0,
                distData.reviewed || 0,
                distData.interview_scheduled || 0,
                distData.accepted || 0,
                distData.rejected || 0
            ];

            new window.Chart(document.getElementById('statusDistributionChart'), {
                type: 'doughnut',
                data: {
                    labels: distLabels,
                    datasets: [{
                        data: distValues,
                        backgroundColor: [
                            '#f59e0b', // pending - amber
                            '#3b82f6', // reviewed - blue
                            '#8b5cf6', // interview_scheduled - purple
                            '#10b981', // accepted - emerald
                            '#ef4444'  // rejected - red
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
