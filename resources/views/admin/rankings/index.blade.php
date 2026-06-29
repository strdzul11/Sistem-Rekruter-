<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Peringkat SAW Pelamar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Hasil Perangkingan Simple Additive Weighting (SAW)</h3>
                    <p class="text-sm text-gray-500">Peringkat kecocokan berdasarkan normalisasi matriks bobot kriteria.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Posisi / Pekerjaan</th>
                                <th class="py-4 px-6">Nama Pelamar</th>
                                <th class="py-4 px-6">Skor SAW</th>
                                <th class="py-4 px-6">Peringkat</th>
                                <th class="py-4 px-6">Status Evaluasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @forelse ($rankings as $rank)
                                <tr>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $rank->jobListing->position }}</div>
                                        <div class="text-xs text-gray-400 font-normal mt-0.5">{{ $rank->jobListing->company }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="text-gray-900 font-semibold">{{ $rank->application->applicant_name ?? ($rank->application->user ? $rank->application->user->name : '-') }}</div>
                                    </td>
                                    <td class="py-4 px-6 text-blue-600 font-bold text-lg">
                                        {{ number_format($rank->saw_score * 100, 2) }}%
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-50 text-blue-800">
                                            #{{ $rank->rank_position }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 uppercase text-xs">
                                        {{ $rank->evaluation_status }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">
                                        Belum ada data perangkingan SAW. Berikan penilaian pelamar terlebih dahulu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
