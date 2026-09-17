<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tinjau Lamaran Kerja') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif

            {{-- ===== Filter Bar ===== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('hrd.applications.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <x-input-label for="q" value="Cari Pelamar" />
                        <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" :value="request('q')" placeholder="Nama / email..." />
                    </div>
                    <div>
                        <x-input-label for="filter_status" value="Status" />
                        <select id="filter_status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Semua Status</option>
                            @foreach(['pending' => 'Pending', 'reviewed' => 'Ditinjau', 'interview_scheduled' => 'Interview Dijadwalkan', 'accepted' => 'Diterima', 'rejected' => 'Ditolak'] as $val => $label)
                                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="filter_job" value="Lowongan" />
                        <select id="filter_job" name="job_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Semua Lowongan</option>
                            @foreach($jobs as $id => $position)
                                <option value="{{ $id }}" {{ request('job_id') == $id ? 'selected' : '' }}>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="date_from" value="Dari Tanggal" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="request('date_from')" />
                    </div>
                    <div>
                        <x-input-label for="date_to" value="Sampai Tanggal" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="request('date_to')" />
                    </div>
                    <div class="sm:col-span-2 lg:col-span-5 flex gap-3">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-5 rounded-lg text-sm">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('hrd.applications.index') }}" class="border border-gray-300 text-gray-600 hover:bg-gray-50 font-semibold py-2 px-5 rounded-lg text-sm">
                            <i class="fas fa-times mr-1"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>

            {{-- ===== Tabel dengan Alpine Bulk Actions ===== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8"
                 x-data="{
                     selected: [],
                     allIds: {{ $applications->pluck('id')->toJson() }},
                     bulkStatus: '',
                     showConfirmModal: false,
                     toggleAll(checked) {
                         this.selected = checked ? [...this.allIds] : [];
                     },
                     isAllSelected() {
                         return this.allIds.length > 0 && this.selected.length === this.allIds.length;
                     },
                     hasUnevaluated: false,
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
                 }"
                 x-init="$watch('selected', val => {
                     if (bulkStatus === 'accepted') {
                         // warning akan ditampilkan di template
                     }
                 })">

                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Kelola Pengajuan Lamaran</h3>
                        <p class="text-sm text-gray-500">Tinjau CV, resume, dan kelola status lamaran pelamar.</p>
                    </div>
                    <div class="text-sm text-gray-400">{{ $applications->total() }} lamaran ditemukan</div>
                </div>

                {{-- Toolbar Bulk Action --}}
                <div x-show="selected.length > 0"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mb-4 flex flex-wrap items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-5 py-3">
                    <span class="text-sm font-semibold text-emerald-700">
                        <i class="fas fa-check-square mr-1"></i>
                        <span x-text="selected.length"></span> lamaran dipilih
                    </span>
                    <select x-model="bulkStatus" class="border-emerald-300 rounded-lg text-sm px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Pilih Status Baru...</option>
                        <option value="pending">Pending</option>
                        <option value="reviewed">Ditinjau</option>
                        <option value="interview_scheduled">Interview Dijadwalkan</option>
                        <option value="accepted">Diterima</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <button type="button"
                            @click="if(bulkStatus) { showConfirmModal = true } else { alert('Pilih status terlebih dahulu.') }"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-1.5 px-4 rounded-lg text-sm">
                        Terapkan
                    </button>
                    {{-- Warning untuk accepted tanpa evaluasi --}}
                    <div x-show="bulkStatus === 'accepted'" class="text-xs text-amber-600 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg flex items-center gap-1">
                        <i class="fas fa-exclamation-triangle"></i>
                        Beberapa lamaran mungkin belum memiliki skor SAW — periksa evaluasi terlebih dahulu.
                    </div>
                </div>

                {{-- Modal Konfirmasi Bulk Action --}}
                <x-modal name="bulk-confirm">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Aksi Massal</h3>
                        <p class="text-sm text-gray-600">
                            Anda akan mengubah status
                            <strong x-text="selected.length"></strong> lamaran
                            menjadi <strong x-text="bulkStatus"></strong>.
                        </p>
                        <p class="text-sm text-gray-500 mt-1">Tindakan ini tidak dapat dibatalkan secara otomatis.</p>
                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button @click="$dispatch('close-modal', 'bulk-confirm'); showConfirmModal = false">
                                Batal
                            </x-secondary-button>
                            <form method="POST" action="{{ route('hrd.applications.bulkUpdate') }}" id="bulkForm">
                                @csrf
                                <input type="hidden" name="status" :value="bulkStatus">
                                <template x-for="id in selected" :key="id">
                                    <input type="hidden" name="application_ids[]" :value="id">
                                </template>
                                <x-primary-button type="submit">
                                    Ya, Ubah Status
                                </x-primary-button>
                            </form>
                        </div>
                    </div>
                </x-modal>

                {{-- Trigger modal saat showConfirmModal berubah --}}
                <span x-effect="if(showConfirmModal) { $dispatch('open-modal', 'bulk-confirm') }"></span>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-3">
                                    <input type="checkbox"
                                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                           @change="toggleAll($event.target.checked)"
                                           :checked="isAllSelected()">
                                </th>
                                <th class="py-4 px-6">Pelamar</th>
                                <th class="py-4 px-6">Lowongan</th>
                                <th class="py-4 px-6">Tipe</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @forelse ($applications as $app)
                                <tr x-bind:class="selected.includes({{ $app->id }}) ? 'bg-emerald-50' : ''">
                                    <td class="py-4 px-3">
                                        <input type="checkbox"
                                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                               value="{{ $app->id }}"
                                               x-model="selected">
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $app->applicant_name ?? ($app->user?->name ?? '-') }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $app->applicant_email ?? ($app->user?->email ?? '-') }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div>{{ $app->jobListing->position }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $app->jobListing->company }}</div>
                                    </td>
                                    <td class="py-4 px-6 uppercase text-xs">{{ $app->application_type }}</td>
                                    <td class="py-4 px-6">
                                        @php
                                            $statusColors = [
                                                'pending'              => 'bg-amber-50 text-amber-700',
                                                'reviewed'             => 'bg-blue-50 text-blue-700',
                                                'interview_scheduled'  => 'bg-purple-50 text-purple-700',
                                                'accepted'             => 'bg-green-50 text-green-700',
                                                'rejected'             => 'bg-red-50 text-red-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium uppercase {{ $statusColors[$app->status] ?? 'bg-gray-50 text-gray-700' }}">
                                            {{ $app->status }}
                                        </span>
                                    </td>
                                     <td class="py-4 px-6 text-right">
                                         <x-action-menu>
                                             <x-action-item onclick="fetchPreview('{{ route('hrd.applications.preview', $app) }}')" icon="fas fa-eye">Preview</x-action-item>
                                             <x-action-item :href="route('hrd.applications.show', $app)" icon="fas fa-external-link-alt">Detail</x-action-item>
                                         </x-action-menu>
                                     </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada lamaran masuk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($applications->hasPages())
                    <div class="mt-6">
                        {{ $applications->links() }}
                    </div>
                @endif
                {{-- Modal Preview Cepat Lamaran --}}
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
                                    <span class="font-bold text-emerald-700 text-base" x-text="previewData?.position"></span>
                                    <span class="text-xs text-gray-400 block mt-0.5" x-text="previewData?.company"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-450 font-semibold block">Nomor Telepon</span>
                                    <span class="font-medium text-gray-800" x-text="previewData?.applicant_phone || '-'"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-450 font-semibold block">Status Lamaran</span>
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xxs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 mt-1" x-text="previewData?.status"></span>
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
                                        <a :href="previewData?.resume_url" class="inline-flex items-center px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl font-bold text-xs transition">
                                            <i class="fas fa-file-download mr-1.5"></i> CV / Resume
                                        </a>
                                    </template>
                                    <a :href="previewData?.detail_url" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white border border-transparent rounded-xl font-bold text-xs transition shadow-sm">
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
