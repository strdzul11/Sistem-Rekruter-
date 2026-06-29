<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Penilaian Pelamar — Detail') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900">{{ $application->applicant_name ?? ($application->user?->name ?? 'Pelamar') }}</h3>
                <p class="text-sm text-gray-500 mt-1">Melamar: <strong>{{ $application->jobListing->position }}</strong></p>
                <p class="text-sm text-gray-500">{{ $application->applicant_email ?? ($application->user?->email ?? '-') }}</p>
            </div>

            {{-- ===== Breakout Skor Per Evaluator ===== --}}
            @if($breakdown->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-blue-500"></i>
                        Transparansi Penilaian SAW
                    </h3>
                    <div class="space-y-4">
                        @foreach($breakdown as $item)
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-sm">{{ $item->criteria_name }}</h4>
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $item->type === 'cost' ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }} font-semibold">
                                            {{ strtoupper($item->type) }}
                                        </span>
                                    </div>
                                    <div class="text-right text-xs text-gray-500">
                                        <div>Bobot: <strong class="text-blue-600">{{ $item->weight }}%</strong></div>
                                        <div>Rata-rata skor: <strong class="text-gray-800">{{ $item->avg_score }}</strong></div>
                                        <div>Kontribusi skor: <strong class="text-indigo-600">{{ number_format($item->weighted_score * 100, 2) }}%</strong></div>
                                    </div>
                                </div>
                                <div class="mt-2 divide-y divide-gray-100">
                                    @foreach($item->evaluators as $ev)
                                        <div class="flex justify-between py-1.5 text-xs text-gray-600">
                                            <span><i class="fas fa-user-circle mr-1 text-gray-400"></i>{{ $ev->name }}</span>
                                            <div class="flex items-center gap-3">
                                                @if($ev->notes)
                                                    <span class="text-gray-400 italic truncate max-w-[120px]" title="{{ $ev->notes }}">{{ $ev->notes }}</span>
                                                @endif
                                                <span class="font-bold text-gray-800 bg-white px-2 py-0.5 rounded border border-gray-200">{{ $ev->score }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @php
                        $totalWeighted = $breakdown->sum('weighted_score');
                        $totalWeight   = $breakdown->sum(fn($i) => $i->weight / 100);
                        $finalScore    = $totalWeight > 0 ? $totalWeighted / $totalWeight : 0;
                    @endphp
                    <div class="mt-4 flex justify-end">
                        <div class="text-right bg-blue-50 border border-blue-200 px-5 py-3 rounded-xl">
                            <div class="text-xs text-blue-600 font-semibold mb-0.5">Skor SAW Final</div>
                            <div class="text-2xl font-extrabold text-blue-700">{{ number_format($finalScore * 100, 2) }}%</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ===== Form Input Penilaian ===== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-star mr-2 text-amber-500"></i>
                    Input Penilaian Anda
                </h3>
                <form method="POST" action="{{ route('admin.evaluations.store') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="application_id" value="{{ $application->id }}">

                    @forelse($criteria as $index => $criterion)
                        @php
                            $existing = $application->evaluations->firstWhere('criteria_id', $criterion->id);
                        @endphp
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $criterion->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $criterion->description }}</p>
                                    <span class="text-xs mt-1 inline-block px-2 py-0.5 rounded-full {{ $criterion->type === 'cost' ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }} font-semibold">
                                        {{ strtoupper($criterion->type) }}
                                    </span>
                                </div>
                                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">Bobot {{ $criterion->weight }}%</span>
                            </div>
                            <input type="hidden" name="evaluations[{{ $index }}][criteria_id]" value="{{ $criterion->id }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Skor ({{ $criterion->min_value }}-{{ $criterion->max_value }})</label>
                                    <input type="number" name="evaluations[{{ $index }}][score]"
                                           min="{{ $criterion->min_value }}" max="{{ $criterion->max_value }}"
                                           value="{{ old('evaluations.'.$index.'.score', $existing?->score) }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                                    <input type="text" name="evaluations[{{ $index }}][notes]"
                                           value="{{ old('evaluations.'.$index.'.notes', $existing?->notes) }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           placeholder="Opsional">
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Belum ada kriteria penilaian aktif.</p>
                    @endforelse

                    @if($criteria->isNotEmpty())
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <a href="{{ route('admin.evaluations.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold text-sm"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl"><i class="fas fa-save mr-2"></i> Simpan Penilaian</button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
