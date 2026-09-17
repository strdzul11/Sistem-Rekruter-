<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Semua Aplikasi Pelamar (Admin)') }}
        </h2>
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
                    <h3 class="text-lg font-bold text-gray-900">Semua Berkas Lamaran Masuk</h3>
                    <p class="text-sm text-gray-500">Lihat detail lamaran, resume CV pelamar, dan status proses seleksi.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Pelamar</th>
                                <th class="py-4 px-6">Pekerjaan yang Dilamar</th>
                                <th class="py-4 px-6">Tipe Pengajuan</th>
                                <th class="py-4 px-6">Dokumen Resume</th>
                                <th class="py-4 px-6">Status Lamaran</th>
                                <th class="py-4 px-6">Tanggal Masuk</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @forelse ($applications as $app)
                                <tr>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $app->applicant_name ?? ($app->user ? $app->user->name : '-') }}</div>
                                        <div class="text-xs text-gray-400 font-normal mt-0.5">{{ $app->applicant_email ?? ($app->user ? $app->user->email : '-') }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="text-gray-900 font-semibold">{{ $app->jobListing->position }}</div>
                                        <div class="text-xs text-gray-400 font-normal mt-0.5">{{ $app->jobListing->company }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide bg-gray-50 text-gray-600">
                                            {{ str_replace('_', ' ', $app->application_type) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($app->resume_path)
                                            <a href="{{ route('admin.applications.resume', $app) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition">
                                                <i class="fas fa-file-download mr-2"></i>Unduh CV
                                            </a>
                                        @else
                                            <span class="text-gray-400">Tidak ada file</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide {{ $app->status === 'accepted' ? 'bg-green-50 text-green-700' : ($app->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700') }}">
                                            {{ $app->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-400 text-xs font-normal">
                                        {{ $app->created_at ? $app->created_at->format('Y-m-d H:i') : '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <x-action-menu>
                                            <x-action-item onclick="fetchPreview('{{ route('admin.applications.preview', $app) }}')" icon="fas fa-eye">Preview</x-action-item>
                                            <x-action-item :href="route('admin.applications.show', $app)" icon="fas fa-external-link-alt">Detail</x-action-item>
                                        </x-action-menu>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">
                                        Belum ada pengajuan lamaran kerja dari kandidat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Modal Preview Cepat Lamaran (Admin) --}}
                <x-modal name="preview-modal" maxWidth="2xl">
                    <div class="p-6">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Preview Cepat Lamaran</h3>
                            <button type="button" @click="$dispatch('close-modal', 'preview-modal')" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        {{-- Loading Skeleton --}}
                        <div x-show="previewLoading" class="space-y-4 animate-pulse py-4">
                            <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="h-3 bg-gray-200 rounded"></div>
                                <div class="h-3 bg-gray-200 rounded"></div>
                            </div>
                            <div class="h-20 bg-gray-200 rounded"></div>
                        </div>

                        {{-- Error Message --}}
                        <div x-show="previewError" class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 text-sm" x-text="previewError"></div>

                        {{-- Main Content --}}
                        <div x-show="!previewLoading && !previewError && previewData" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-xs text-gray-405 font-bold block mb-1">PELAMAR</span>
                                    <span class="font-bold text-gray-900 text-base" x-text="previewData?.applicant_name"></span>
                                    <span class="text-xs text-gray-400 block mt-0.5" x-text="previewData?.applicant_email"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-405 font-bold block mb-1">LOWONGAN KERJA</span>
                                    <span class="font-bold text-blue-700 text-base" x-text="previewData?.position"></span>
                                    <span class="text-xs text-gray-400 block mt-0.5" x-text="previewData?.company"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-450 font-semibold block">Nomor Telepon</span>
                                    <span class="font-medium text-gray-800" x-text="previewData?.applicant_phone || '-'"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-450 font-semibold block">Status Lamaran</span>
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xxs font-bold uppercase tracking-wider bg-blue-50 text-blue-700 mt-1" x-text="previewData?.status"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-450 font-semibold block">Pengalaman Kerja</span>
                                    <span class="font-medium text-gray-800" x-text="previewData?.experience || '-'"></span>
                                </div>
                            </div>

                            <div x-show="previewData?.cover_letter" class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                                <span class="text-xs text-gray-400 font-bold uppercase block mb-1">Cover Letter</span>
                                <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed" x-text="previewData?.cover_letter"></p>
                            </div>

                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <x-secondary-button @click="$dispatch('close-modal', 'preview-modal')">Tutup</x-secondary-button>
                                <div class="flex gap-2">
                                    <template x-if="previewData?.resume_url">
                                        <a :href="previewData?.resume_url" class="inline-flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl font-bold text-xs transition">
                                            <i class="fas fa-file-download mr-1.5"></i> CV / Resume
                                        </a>
                                    </template>
                                    <a :href="previewData?.detail_url" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white border border-transparent rounded-xl font-bold text-xs transition shadow-sm">
                                        <i class="fas fa-external-link-alt mr-1.5"></i> Detail Lengkap
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-modal>
            </div>
        </div>
    </div>
</x-app-layout>
