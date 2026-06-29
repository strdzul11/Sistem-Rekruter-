<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Peringkat SAW Pelamar') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- ===== Filter Bar ===== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('hrd.rankings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="filter_rank_job" value="Lowongan" />
                        <select id="filter_rank_job" name="job_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Semua Lowongan</option>
                            @foreach($jobs as $id => $position)
                                <option value="{{ $id }}" {{ request('job_id') == $id ? 'selected' : '' }}>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="filter_eval_status" value="Status Evaluasi" />
                        <select id="filter_eval_status" name="evaluation_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('evaluation_status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="final" {{ request('evaluation_status') === 'final' ? 'selected' : '' }}>Final</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="min_score" value="Skor Minimum (%)" />
                        <x-text-input id="min_score" name="min_score" type="number" class="mt-1 block w-full"
                                      :value="request('min_score')" placeholder="Contoh: 60" min="0" max="100" />
                    </div>
                    <div class="flex items-end gap-3">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-5 rounded-lg text-sm">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('hrd.rankings.index') }}" class="border border-gray-300 text-gray-600 hover:bg-gray-50 font-semibold py-2 px-5 rounded-lg text-sm">
                            <i class="fas fa-times mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Hasil Perangkingan SAW</h3>
                    <p class="text-sm text-gray-500">Peringkat kecocokan berdasarkan bobot kriteria yang ditetapkan Admin.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Posisi</th>
                                <th class="py-4 px-6">Pelamar</th>
                                <th class="py-4 px-6">Skor SAW</th>
                                <th class="py-4 px-6">Peringkat</th>
                                <th class="py-4 px-6">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @forelse ($rankings as $rank)
                                <tr>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $rank->jobListing->position }}</div>
                                    </td>
                                    <td class="py-4 px-6 font-semibold">{{ $rank->application->applicant_name ?? ($rank->application->user?->name ?? '-') }}</td>
                                    <td class="py-4 px-6 text-emerald-600 font-bold text-lg">{{ number_format($rank->saw_score * 100, 2) }}%</td>
                                    <td class="py-4 px-6"><span class="inline-flex px-3 py-1 rounded-full text-sm font-bold bg-emerald-50 text-emerald-800">#{{ $rank->rank_position }}</span></td>
                                    <td class="py-4 px-6 uppercase text-xs">{{ $rank->evaluation_status }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada data ranking. Berikan penilaian pelamar terlebih dahulu.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($rankings->hasPages())
                    <div class="mt-6">
                        {{ $rankings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
