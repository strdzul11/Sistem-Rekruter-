<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Analitik Sistem (Admin)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Header Actions --}}
            <div class="flex justify-between items-center mb-4 print:hidden">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Analitik & Statistik Sistem</h3>
                    <p class="text-sm text-gray-500">Visualisasi data lowongan, pendaftaran, dan aktivitas lamaran.</p>
                </div>
                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-600 mr-4"><i class="fas fa-briefcase text-xl"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase">Total Lowongan</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $summary['total_jobs'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-xl bg-green-50 text-green-600 mr-4"><i class="fas fa-toggle-on text-xl"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase">Lowongan Aktif</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $summary['active_jobs'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-xl bg-purple-50 text-purple-600 mr-4"><i class="fas fa-file-invoice text-xl"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase">Total Lamaran</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $summary['total_applications'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-xl bg-amber-50 text-amber-600 mr-4"><i class="fas fa-users text-xl"></i></div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase">Pelamar Terdaftar</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $summary['registered_users'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Charts Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Line Chart --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="font-bold text-gray-900 text-sm mb-4"><i class="fas fa-chart-line mr-2 text-blue-500"></i>Tren Lamaran Bulanan (12 Bulan Terakhir)</h4>
                    <div class="relative h-72">
                        <canvas id="monthlyTrendChartAdmin"></canvas>
                    </div>
                </div>

                {{-- Bar Chart --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h4 class="font-bold text-gray-900 text-sm mb-4"><i class="fas fa-chart-bar mr-2 text-blue-500"></i>Jumlah Lamaran per Lowongan</h4>
                    <div class="relative h-72">
                        <canvas id="jobsBarChartAdmin"></canvas>
                    </div>
                </div>
            </div>

            {{-- Table Detail --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h4 class="font-bold text-gray-900 text-sm mb-4"><i class="fas fa-list-ul mr-2 text-blue-500"></i>Detail Statistik Lowongan Kerja</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider">
                                <th class="pb-3 px-6">Nama Lowongan</th>
                                <th class="pb-3 px-6">Perusahaan</th>
                                <th class="pb-3 px-6">Batas Tanggal</th>
                                <th class="pb-3 px-6">Status</th>
                                <th class="pb-3 px-6 text-right">Jumlah Lamaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium">
                            @forelse($jobsReport as $job)
                                <tr>
                                    <td class="py-3 px-6 font-bold text-gray-900">{{ $job->position }}</td>
                                    <td class="py-3 px-6 text-gray-500">{{ $job->company }}</td>
                                    <td class="py-3 px-6 text-gray-550">{{ $job->application_deadline ? $job->application_deadline->format('d/m/Y') : 'Tanpa batas' }}</td>
                                    <td class="py-3 px-6">
                                        <span class="px-2 py-0.5 rounded-full text-xxs font-semibold uppercase {{ $job->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $job->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6 text-right font-bold text-blue-600">{{ $job->applications_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-gray-400">Belum ada lowongan terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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

            new window.Chart(document.getElementById('monthlyTrendChartAdmin'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Jumlah Lamaran',
                        data: trendValues,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
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

            // Data Bar Chart Lamaran Per Lowongan
            const jobsData = @js($jobsReport);
            const jobLabels = jobsData.map(j => j.position);
            const jobValues = jobsData.map(j => j.applications_count);

            new window.Chart(document.getElementById('jobsBarChartAdmin'), {
                type: 'bar',
                data: {
                    labels: jobLabels,
                    datasets: [{
                        label: 'Jumlah Lamaran',
                        data: jobValues,
                        backgroundColor: '#3b82f6',
                        borderRadius: 6
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
        });
    </script>
    @endpush
</x-app-layout>
