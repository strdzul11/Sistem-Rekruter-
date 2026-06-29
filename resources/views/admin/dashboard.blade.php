<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Admin Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl overflow-hidden mb-8 text-white p-8 relative">
                <div class="relative z-10">
                    <h1 class="text-3xl font-extrabold tracking-tight mb-2">Selamat Datang, {{ Auth::user()->name }}!</h1>
                    <p class="text-blue-100 text-lg max-w-xl">Anda masuk sebagai <strong>Administrator</strong>. Kelola pengguna, konfigurasi kriteria SAW, template interview, dan pengaturan sistem aplikasi.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-lg bg-blue-50 text-blue-600 mr-4"><i class="fas fa-users text-xl"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase">Total Pengguna</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $userCount }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-lg bg-green-50 text-green-600 mr-4"><i class="fas fa-cogs text-xl"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase">Kriteria SAW Aktif</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $criteriaCount }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-lg bg-purple-50 text-purple-600 mr-4"><i class="fas fa-question-circle text-xl"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase">Template Pertanyaan</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $questionCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Akses Administrator</h3>
                <p class="text-sm text-gray-500 mb-6">Fitur operasional rekrutmen (lowongan, lamaran, penilaian) dikelola oleh tim HRD.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="{{ route('admin.users.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700"><i class="fas fa-users mr-2 text-blue-500"></i>Manajemen User</h4>
                        <p class="text-sm text-gray-500 mt-2">Kelola akun Admin, HRD, dan Pelamar.</p>
                    </a>
                    <a href="{{ route('admin.criteria.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700"><i class="fas fa-cogs mr-2 text-blue-500"></i>Kriteria SAW</h4>
                        <p class="text-sm text-gray-500 mt-2">Atur bobot kriteria penilaian pelamar.</p>
                    </a>
                    <a href="{{ route('admin.questions.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700"><i class="fas fa-question-circle mr-2 text-blue-500"></i>Template Pertanyaan</h4>
                        <p class="text-sm text-gray-500 mt-2">Kelola bank pertanyaan wawancara.</p>
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700"><i class="fas fa-sliders-h mr-2 text-blue-500"></i>Pengaturan Sistem</h4>
                        <p class="text-sm text-gray-500 mt-2">Logo, nama perusahaan, dan landing page.</p>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700"><i class="fas fa-chart-bar mr-2 text-blue-500"></i>Laporan & Statistik</h4>
                        <p class="text-sm text-gray-500 mt-2">Rekap lamaran, conversion rate, aktivitas HRD.</p>
                    </a>
                    <a href="{{ route('admin.audit_logs.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700"><i class="fas fa-history mr-2 text-blue-500"></i>Audit Log</h4>
                        <p class="text-sm text-gray-500 mt-2">Jejak perubahan data penting.</p>
                    </a>
                    <a href="{{ route('admin.backups.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700"><i class="fas fa-database mr-2 text-blue-500"></i>Backup & Restore</h4>
                        <p class="text-sm text-gray-500 mt-2">Keamanan dan pemulihan data.</p>
                    </a>
                    <a href="{{ route('admin.permissions.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700"><i class="fas fa-shield-alt mr-2 text-blue-500"></i>Hak Akses Role</h4>
                        <p class="text-sm text-gray-500 mt-2">Izin granular per fitur untuk HRD & Pelamar.</p>
                    </a>
                    <a href="{{ route('admin.email_settings.index') }}" class="p-6 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-100 transition group">
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700"><i class="fas fa-envelope mr-2 text-blue-500"></i>Notifikasi Email</h4>
                        <p class="text-sm text-gray-500 mt-2">Konfigurasi SMTP dan kirim email test.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
