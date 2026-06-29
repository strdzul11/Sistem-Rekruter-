<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('HRD Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl shadow-xl overflow-hidden mb-8 text-white p-8 relative">
                <div class="relative z-10">
                    <h1 class="text-3xl font-extrabold tracking-tight mb-2">Halo, {{ Auth::user()->name }}!</h1>
                    <p class="text-emerald-100 text-lg max-w-xl">Anda masuk sebagai <strong>HRD</strong>. Kelola lowongan, tinjau lamaran, beri penilaian SAW, dan atur jadwal wawancara.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600 mr-4"><i class="fas fa-file-alt text-xl"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase">Lamaran Masuk</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $applicationCount }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-lg bg-teal-50 text-teal-600 mr-4"><i class="fas fa-video text-xl"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase">Jadwal Interview</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $interviewCount }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-lg bg-cyan-50 text-cyan-600 mr-4"><i class="fas fa-briefcase text-xl"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase">Lowongan Aktif</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $activeJobCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Menu Operasional HRD</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="{{ route('hrd.jobs.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-emerald-50 hover:border-emerald-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-emerald-700"><i class="fas fa-briefcase mr-2 text-emerald-500"></i>Lowongan Kerja</h4>
                        <p class="text-sm text-gray-500 mt-2">Buat, ubah, dan kelola posting lowongan.</p>
                    </a>
                    <a href="{{ route('hrd.applications.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-emerald-50 hover:border-emerald-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-emerald-700"><i class="fas fa-file-alt mr-2 text-emerald-500"></i>Aplikasi Pelamar</h4>
                        <p class="text-sm text-gray-500 mt-2">Tinjau lamaran dan ubah status seleksi.</p>
                    </a>
                    <a href="{{ route('hrd.evaluations.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-emerald-50 hover:border-emerald-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-emerald-700"><i class="fas fa-star mr-2 text-emerald-500"></i>Penilaian Pelamar</h4>
                        <p class="text-sm text-gray-500 mt-2">Input nilai kriteria untuk perhitungan SAW.</p>
                    </a>
                    <a href="{{ route('hrd.rankings.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-emerald-50 hover:border-emerald-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-emerald-700"><i class="fas fa-trophy mr-2 text-emerald-500"></i>Ranking SAW</h4>
                        <p class="text-sm text-gray-500 mt-2">Lihat peringkat pelamar terbaik per lowongan.</p>
                    </a>
                    <a href="{{ route('hrd.interviews.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-emerald-50 hover:border-emerald-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-emerald-700"><i class="fas fa-video mr-2 text-emerald-500"></i>Manajemen Interview</h4>
                        <p class="text-sm text-gray-500 mt-2">Kelola jadwal wawancara pelamar.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
