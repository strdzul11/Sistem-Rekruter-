<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Hasil Penilaian Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Nilai Kecocokan SAW</h3>
                    <p class="text-sm text-gray-500">Nilai kecocokan kualifikasi Anda dihitung secara matematis menggunakan algoritma SAW.</p>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-8 text-center text-gray-500">
                    Nilai kecocokan lamaran Anda belum dipublikasikan atau masih berstatus draft peninjauan HRD. Silakan cek kembali secara berkala.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
