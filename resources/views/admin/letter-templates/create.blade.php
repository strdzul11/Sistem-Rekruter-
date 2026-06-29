<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Template Surat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Buat Template Baru</h3>
                        <p class="text-sm text-gray-500">Konfigurasi template email/surat yang akan digenerate otomatis.</p>
                    </div>
                    <a href="{{ route('admin.letter-templates.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>

                {{-- Hint Placeholder --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6 text-sm text-blue-800">
                    <p class="font-bold mb-2"><i class="fas fa-info-circle mr-1"></i> Placeholder yang Tersedia:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li><code>@{{applicant_name}}</code> — Akan digantikan dengan nama lengkap pelamar.</li>
                        <li><code>@{{position}}</code> — Akan digantikan dengan posisi/jabatan lowongan yang dilamar.</li>
                        <li><code>@{{company_name}}</code> — Akan digantikan dengan nama perusahaan dari pengaturan sistem.</li>
                        <li><code>@{{date}}</code> — Akan digantikan dengan tanggal hari ini dalam format bahasa Indonesia (e.g. 27 Juni 2026).</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('admin.letter-templates.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nama Template --}}
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700">Nama Template</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Contoh: Surat Penerimaan Resmi">
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        {{-- Tipe Surat --}}
                        <div class="space-y-2">
                            <label for="type" class="block text-sm font-semibold text-gray-700">Tipe Surat</label>
                            <select id="type" name="type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="acceptance" {{ old('type') === 'acceptance' ? 'selected' : '' }}>Penerimaan (Acceptance)</option>
                                <option value="rejection" {{ old('type') === 'rejection' ? 'selected' : '' }}>Penolakan (Rejection)</option>
                                <option value="interview_invitation" {{ old('type') === 'interview_invitation' ? 'selected' : '' }}>Undangan Interview (Interview Invitation)</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Subjek --}}
                    <div class="space-y-2">
                        <label for="subject" class="block text-sm font-semibold text-gray-700">Subjek Surat / Email</label>
                        <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: Pemberitahuan Hasil Seleksi Lamaran untuk Posisi {{position}}">
                        <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                    </div>

                    {{-- Isi Surat --}}
                    <div class="space-y-2">
                        <label for="body" class="block text-sm font-semibold text-gray-700">Isi Surat / Dokumen</label>
                        <textarea id="body" name="body" rows="12" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                                  placeholder="Tulis isi surat di sini. Gunakan tag HTML dasar jika diperlukan. Contoh:
Dear {{applicant_name}},

Selamat! Anda terpilih untuk bergabung dengan {{company_name}} pada posisi {{position}}..."></textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-1" />
                    </div>

                    {{-- Status --}}
                    <div class="space-y-2">
                        <label for="is_active" class="block text-sm font-semibold text-gray-700">Status</label>
                        <select id="is_active" name="is_active" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Non-aktif</option>
                        </select>
                        <x-input-error :messages="$errors->get('is_active')" class="mt-1" />
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition duration-200">
                            Simpan Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
