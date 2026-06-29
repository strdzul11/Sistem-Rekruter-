<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Alert success -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
                    <i class="fas fa-check-circle mr-3 text-lg"></i>
                    <div>
                        <p class="font-bold text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-8 border-b border-gray-100 pb-4">
                    <h3 class="text-xl font-bold text-gray-900">Konfigurasi Aplikasi & Landing Page</h3>
                    <p class="text-sm text-gray-500">Sesuaikan informasi instansi, logo, dan konten halaman depan aplikasi Anda.</p>
                </div>

                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    <!-- SECTION 1: Identitas Perusahaan -->
                    <div class="space-y-6">
                        <h4 class="text-md font-bold text-blue-600 uppercase tracking-wider flex items-center">
                            <i class="fas fa-building mr-2"></i> Identitas Perusahaan
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                            <!-- Logo Preview & Upload -->
                            <div class="md:col-span-1 flex flex-col items-center p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-xs font-semibold text-gray-500 mb-3">Logo Aktif</span>
                                <div class="w-24 h-24 bg-white rounded-2xl shadow-sm border border-gray-200 flex items-center justify-center p-3 mb-4 overflow-hidden">
                                    <img id="logo_preview" src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="Logo" class="max-w-full max-h-full object-contain">
                                </div>
                                <label class="w-full text-center bg-blue-50 text-blue-600 text-xs font-bold py-2 px-3 rounded-lg cursor-pointer hover:bg-blue-100 transition duration-200">
                                    <i class="fas fa-upload mr-1"></i> <span id="upload_label_text">Unggah Logo Baru</span>
                                    <input type="file" id="logo_input" name="company_logo" class="hidden" accept="image/*">
                                </label>
                                <span class="text-[10px] text-gray-400 mt-2 text-center">Rekomendasi format PNG/JPG, maks. 2MB</span>
                                @error('company_logo')
                                    <span class="text-red-500 text-xs mt-1 text-center">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Name input -->
                            <div class="md:col-span-2 space-y-2">
                                <label for="company_name" class="block text-sm font-semibold text-gray-700">Nama Perusahaan / PT</label>
                                <input type="text" id="company_name" name="company_name" 
                                       value="{{ old('company_name', $settings['company_name'] ?? '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                       placeholder="Masukkan nama perusahaan Anda">
                                @error('company_name')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Landing Page Configuration -->
                    <div class="space-y-6 pt-6 border-t border-gray-100">
                        <h4 class="text-md font-bold text-blue-600 uppercase tracking-wider flex items-center">
                            <i class="fas fa-desktop mr-2"></i> Konten Landing Page
                        </h4>

                        <!-- Hero Title -->
                        <div class="space-y-2">
                            <label for="landing_hero_title" class="block text-sm font-semibold text-gray-700">Judul Utama (Hero Title)</label>
                            <input type="text" id="landing_hero_title" name="landing_hero_title" 
                                   value="{{ old('landing_hero_title', $settings['landing_hero_title'] ?? '') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                   placeholder="Contoh: Bergabunglah dengan Tim Kami">
                            @error('landing_hero_title')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Hero Subtitle -->
                        <div class="space-y-2">
                            <label for="landing_hero_subtitle" class="block text-sm font-semibold text-gray-700">Sub-Judul Utama (Hero Subtitle)</label>
                            <p class="text-xs text-gray-500">Gunakan <code class="bg-gray-100 px-1 rounded">{company_name}</code> agar nama perusahaan otomatis mengikuti pengaturan di atas.</p>
                            <textarea id="landing_hero_subtitle" name="landing_hero_subtitle" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                      placeholder="Masukkan subjudul deskripsi hero">{{ old('landing_hero_subtitle', $settings['landing_hero_subtitle'] ?? '') }}</textarea>
                            @error('landing_hero_subtitle')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- About Us Title -->
                        <div class="space-y-2">
                            <label for="landing_about_title" class="block text-sm font-semibold text-gray-700">Judul Bagian Tentang Kami (About Us Title)</label>
                            <input type="text" id="landing_about_title" name="landing_about_title" 
                                   value="{{ old('landing_about_title', $settings['landing_about_title'] ?? '') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                   placeholder="Contoh: Tentang Kami / Our Story">
                            @error('landing_about_title')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- About Us Description -->
                        <div class="space-y-2">
                            <label for="landing_about_description" class="block text-sm font-semibold text-gray-700">Deskripsi Tentang Kami (About Us Description)</label>
                            <textarea id="landing_about_description" name="landing_about_description" rows="8"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                      placeholder="Masukkan cerita/sejarah/profil detail perusahaan">{{ old('landing_about_description', $settings['landing_about_description'] ?? '') }}</textarea>
                            @error('landing_about_description')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl shadow-md transition duration-200">
                            <i class="fas fa-save mr-2"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('logo_input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('logo_preview').src = event.target.result;
                };
                reader.readAsDataURL(file);
                
                const labelText = document.getElementById('upload_label_text');
                if (labelText) {
                    labelText.textContent = 'Terpilih: ' + file.name.substring(0, 15) + (file.name.length > 15 ? '...' : '');
                }
            }
        });
    </script>
</x-app-layout>
