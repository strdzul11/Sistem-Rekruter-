<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Penilaian Pelamar') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8"
                 x-data="{
                     previewData: null,
                     previewLoading: false,
                     previewError: null,
                     fetchPreview(url) {
                         this.previewLoading = true;
                         this.previewData = null;
                         this.previewError = null;
                         this.$dispatch('open-modal', 'preview-modal');
                         fetch(url)
                             .then(response => {
                                 if (!response.ok) {
                                     throw new Error('Gagal mengambil data preview.');
                                 }
                                 return response.json();
                             })
                             .then(data => {
                                 this.previewData = data;
                                 this.previewLoading = false;
                             })
                             .catch(err => {
                                 this.previewError = err.message;
                                 this.previewLoading = false;
                             });
                     }
                 }">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Daftar Lamaran untuk Dinilai</h3>
                    <p class="text-sm text-gray-500">Beri penilaian kriteria pelamar untuk perhitungan ranking SAW.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Pelamar</th>
                                <th class="py-4 px-6">Posisi</th>
                                <th class="py-4 px-6">Status Nilai</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @foreach ($applications as $app)
                                <tr>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $app->applicant_name ?? ($app->user?->name ?? '-') }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $app->applicant_email ?? ($app->user?->email ?? '-') }}</div>
                                    </td>
                                    <td class="py-4 px-6">{{ $app->jobListing->position }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $app->evaluations->count() > 0 ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                                            {{ $app->evaluations->count() > 0 ? 'Sudah Dinilai (' . $app->evaluations->count() . ')' : 'Belum Dinilai' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <button type="button" @click="fetchPreview('{{ route('hrd.evaluations.preview', $app) }}')" class="text-gray-500 hover:text-gray-700 font-semibold text-sm">
                                            <i class="fas fa-eye mr-1"></i>Preview
                                        </button>
                                        <a href="{{ route('hrd.evaluations.show', $app) }}" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-md">
                                            Beri Nilai &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Modal Preview Cepat Penilaian SAW (HRD) --}}
                <x-modal name="preview-modal" maxWidth="2xl">
                    <div class="p-6">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Preview Hasil Penilaian SAW</h3>
                            <button type="button" @click="$dispatch('close-modal', 'preview-modal')" class="text-gray-400 hover:text-gray-650">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        {{-- Loading Skeleton --}}
                        <div x-show="previewLoading" class="space-y-4 animate-pulse py-4">
                            <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                            <div class="h-10 bg-gray-200 rounded"></div>
                            <div class="h-20 bg-gray-200 rounded"></div>
                        </div>

                        {{-- Error Message --}}
                        <div x-show="previewError" class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 text-sm" x-text="previewError"></div>

                        {{-- Main Content --}}
                        <div x-show="!previewLoading && !previewError && previewData" class="space-y-6">
                            <div class="flex justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-base" x-text="previewData?.applicant_name"></h4>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="previewData?.position + ' (' + previewData?.company + ')'"></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xxs text-gray-450 block font-bold uppercase tracking-wider">SKOR SAW TOTAL</span>
                                    <span class="text-2xl font-extrabold text-emerald-600" x-text="previewData?.total_score"></span>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Rincian Nilai per Kriteria</h4>
                                <div class="space-y-3">
                                    <template x-for="item in previewData?.breakdown" :key="item.criteria_id">
                                        <div class="bg-white p-3.5 rounded-xl border border-gray-150 shadow-xs">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <span class="font-bold text-gray-900 text-sm" x-text="item.criteria_name"></span>
                                                    <span class="text-xxs font-bold uppercase px-2 py-0.5 rounded-full ml-2 border" :class="item.type === 'benefit' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-red-50 text-red-700 border-red-100'" x-text="item.type"></span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-xs font-bold text-gray-800" x-text="'Rata-Rata: ' + item.avg_score"></span>
                                                    <span class="text-xxs text-gray-400 block" x-text="'Bobot: ' + item.weight + '%'"></span>
                                                </div>
                                            </div>

                                            <div class="mt-3 border-t border-gray-50 pt-2" x-show="item.evaluators && item.evaluators.length > 0">
                                                <span class="text-xxs text-gray-400 font-bold block mb-1">KOMENTAR EVALUATOR:</span>
                                                <div class="space-y-1">
                                                    <template x-for="e in item.evaluators" :key="e.name">
                                                        <div class="text-xs bg-gray-50 p-2 rounded-lg">
                                                            <strong x-text="e.name + ' (Skor: ' + e.score + ')'" class="text-gray-800"></strong>
                                                            <p x-text="e.notes || 'Tidak ada catatan.'" class="text-gray-600 mt-0.5 italic"></p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <x-secondary-button @click="$dispatch('close-modal', 'preview-modal')">Tutup</x-secondary-button>
                                <a :href="previewData?.detail_url" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white border border-transparent rounded-xl font-bold text-xs transition shadow-sm">
                                    Lihat Detail & Input Nilai Baru &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </x-modal>
            </div>
        </div>
    </div>
</x-app-layout>
