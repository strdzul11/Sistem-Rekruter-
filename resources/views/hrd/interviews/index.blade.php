<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Jadwal Wawancara') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Jadwal Sesi Interview</h3>
                        <p class="text-sm text-gray-500">Kelola janji temu wawancara antara interviewer dan pelamar.</p>
                    </div>
                    <div>
                        <a href="{{ route('hrd.interviews.create') }}" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition duration-150 shadow-sm">
                            <i class="fas fa-plus mr-2"></i> Tawarkan Jadwal
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Pelamar</th>
                                <th class="py-4 px-6">Pewawancara</th>
                                <th class="py-4 px-6">Tipe & Durasi</th>
                                <th class="py-4 px-6">Waktu</th>
                                <th class="py-4 px-6">Status & Opsi</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @forelse ($interviews as $interview)
                                <tr>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $interview->application->applicant_name ?? ($interview->application->user?->name ?? '-') }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Lowongan: {{ $interview->application->jobListing->position }}</div>
                                    </td>
                                    <td class="py-4 px-6">{{ $interview->interviewer->name }}</td>
                                    <td class="py-4 px-6">
                                        <span class="uppercase text-xs font-bold px-2 py-0.5 rounded bg-gray-150 text-gray-700 border border-gray-250">{{ $interview->interview_type }}</span>
                                        <div class="text-xs text-gray-400 mt-1">{{ $interview->duration_minutes }} menit</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div>{{ $interview->interview_date->translatedFormat('d M Y') }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $interview->interview_date->translatedFormat('H:i') }} ({{ $interview->timezone }})</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        @php
                                            $statusColors = [
                                                'scheduled' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                'confirmed' => 'bg-green-50 text-green-700 border-green-100',
                                                'completed' => 'bg-blue-50 text-blue-700 border-blue-100',
                                                'cancelled' => 'bg-red-50 text-red-700 border-red-100',
                                                'rescheduled' => 'bg-purple-50 text-purple-700 border-purple-100',
                                            ];
                                        @endphp
                                        <div class="flex flex-col gap-1 items-start">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold uppercase border {{ $statusColors[$interview->status] ?? 'bg-gray-50 text-gray-700 border-gray-100' }}">
                                                {{ $interview->status }}
                                            </span>
                                            @if($interview->is_proposed)
                                                <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-100 font-semibold" title="Menunggu pilihan/konfirmasi dari pelamar">
                                                    <i class="fas fa-clock mr-1 animate-pulse"></i> Ditawarkan
                                                </span>
                                            @else
                                                <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 font-semibold">
                                                    <i class="fas fa-check-circle mr-1"></i> Dipilih Kandidat
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <x-action-menu>
                                            <x-action-item :href="route('hrd.interviews.show', $interview)" icon="fas fa-eye">Detail</x-action-item>
                                        </x-action-menu>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada jadwal wawancara.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($interviews->hasPages())
                    <div class="mt-6">
                        {{ $interviews->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
