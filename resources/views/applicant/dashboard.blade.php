<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Applicant Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl shadow-xl overflow-hidden mb-8 text-white p-8 relative">
                <div class="relative z-10">
                    <h1 class="text-3xl font-extrabold tracking-tight mb-2">Selamat Datang, {{ Auth::user()->name }}!</h1>
                    <p class="text-purple-100 text-lg max-w-xl">Lengkapi profil portofolio Anda, pantau status lamaran kerja Anda secara transparan, dan periksa jadwal interview di sini.</p>
                </div>
                <!-- Background decoration -->
                <div class="absolute right-0 bottom-0 opacity-10 transform translate-y-6 translate-x-6 z-0">
                    <svg class="w-80 h-80" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-2.283 1 1 0 011-.775zM8.828 10L8 9.645v6.33a8.96 8.96 0 00-2-.18V10.5l-.828-.355A9.052 9.052 0 003 12.279V18a2 2 0 002 2h10a2 2 0 002-2v-5.721a9.055 9.055 0 00-8.172-2.279z"/></svg>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition duration-300 hover:shadow-md">
                    <div class="p-3 rounded-lg bg-purple-50 text-purple-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Lamaran Saya</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition duration-300 hover:shadow-md">
                    <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Jadwal Interview</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">Belum Ada</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition duration-300 hover:shadow-md">
                    <div class="p-3 rounded-lg bg-pink-50 text-pink-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Skor SAW Ranking</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">-</p>
                    </div>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Menu Pelamar</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="{{ route('applicant.profile') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 flex flex-col justify-between hover:bg-purple-50 hover:border-purple-100 transition duration-300 group">
                        <div>
                            <h4 class="font-bold text-gray-900 group-hover:text-purple-700 transition">Profil & CV Saya</h4>
                            <p class="text-sm text-gray-500 mt-2">Perbarui riwayat pendidikan, pengalaman kerja, skill, dan tautan sosial media Anda.</p>
                        </div>
                        <span class="text-purple-600 text-sm font-semibold mt-4 flex items-center group-hover:translate-x-1 transition">Kelola &rarr;</span>
                    </a>

                    <a href="{{ route('applicant.interviews') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 flex flex-col justify-between hover:bg-purple-50 hover:border-purple-100 transition duration-300 group">
                        <div>
                            <h4 class="font-bold text-gray-900 group-hover:text-purple-700 transition">Jadwal Interview</h4>
                            <p class="text-sm text-gray-500 mt-2">Lihat tanggal wawancara, link video call, dan notes yang diberikan oleh pewawancara.</p>
                        </div>
                        <span class="text-purple-600 text-sm font-semibold mt-4 flex items-center group-hover:translate-x-1 transition">Lihat &rarr;</span>
                    </a>

                    <a href="{{ route('applicant.evaluation_results') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 flex flex-col justify-between hover:bg-purple-50 hover:border-purple-100 transition duration-300 group">
                        <div>
                            <h4 class="font-bold text-gray-900 group-hover:text-purple-700 transition">Hasil Penilaian SAW</h4>
                            <p class="text-sm text-gray-500 mt-2">Lihat skor kecocokan lamaran Anda terhadap kriteria posisi yang dilamar secara transparan.</p>
                        </div>
                        <span class="text-purple-600 text-sm font-semibold mt-4 flex items-center group-hover:translate-x-1 transition">Lihat Hasil &rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
