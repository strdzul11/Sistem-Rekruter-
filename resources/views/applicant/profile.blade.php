<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Biodata & Curriculum Vitae</h3>
                    <p class="text-sm text-gray-500">Perbarui data diri untuk mempermudah HRD menilai kecocokan kualifikasi Anda.</p>
                </div>

                <div class="space-y-6 max-w-xl">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                        <input type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" value="{{ Auth::user()->name }}" disabled>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Email Utama</label>
                        <input type="email" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" value="{{ Auth::user()->email }}" disabled>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nomor Telepon / WhatsApp</label>
                        <input type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" value="{{ Auth::user()->phone ?? 'Belum Diisi' }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Pendidikan Terakhir</label>
                        <textarea rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Contoh: S1 Teknik Informatika, Universitas XYZ (IPK: 3.50)"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Pengalaman Kerja</label>
                        <textarea rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Contoh: 2 Tahun sebagai Junior Web Developer di PT. ABC"></textarea>
                    </div>

                    <button class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-2 rounded-lg transition duration-200">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
