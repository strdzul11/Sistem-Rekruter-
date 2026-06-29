<div class="w-64 bg-white shadow-lg flex flex-col h-screen border-r border-gray-100 shrink-0">
    <!-- Sidebar Header (Logo & Brand) -->
    <div class="p-6 border-b border-gray-50 flex items-center">
        <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}" class="w-10 h-10 object-contain">
        <h1 class="ml-3 text-sm font-bold text-gray-900 leading-tight uppercase tracking-wider">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</h1>
    </div>

    <!-- Navigation links -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto space-y-1">
        @if(Auth::user()->role === 'admin')
            {{-- ADMIN: Konfigurasi Sistem --}}
            <div class="px-3 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Administrasi Sistem</p>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                Dashboard
            </a>

            <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-users w-5 mr-3"></i>
                Manajemen User
            </a>

            <div class="px-3 py-2 mt-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Konfigurasi Penilaian</p>
            </div>

            <a href="{{ route('admin.criteria.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.criteria.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-cogs w-5 mr-3"></i>
                Kriteria Penilaian SAW
            </a>

            <a href="{{ route('admin.questions.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.questions.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-question-circle w-5 mr-3"></i>
                Template Pertanyaan
            </a>

            <a href="{{ route('admin.letter-templates.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.letter-templates.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-file-signature w-5 mr-3"></i>
                Template Surat
            </a>

            <div class="px-3 py-2 mt-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pengaturan</p>
            </div>

            <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-sliders-h w-5 mr-3"></i>
                Pengaturan Sistem
            </a>

            <div class="px-3 py-2 mt-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Modul Lanjutan</p>
            </div>

            <a href="{{ route('admin.reports.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-chart-bar w-5 mr-3"></i>
                Laporan & Statistik
            </a>

            <a href="{{ route('admin.audit_logs.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.audit_logs.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-history w-5 mr-3"></i>
                Audit Log
            </a>

            <a href="{{ route('admin.backups.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.backups.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-database w-5 mr-3"></i>
                Backup & Restore
            </a>

            <a href="{{ route('admin.permissions.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.permissions.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-shield-alt w-5 mr-3"></i>
                Hak Akses Role
            </a>

            <a href="{{ route('admin.email_settings.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('admin.email_settings.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-envelope w-5 mr-3"></i>
                Notifikasi Email
            </a>

        @elseif(Auth::user()->role === 'hrd')
            {{-- HRD: Operasional Rekrutmen --}}
            <div class="px-3 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menu HRD</p>
            </div>

            <a href="{{ route('hrd.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('hrd.dashboard') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                Dashboard
            </a>

            <a href="{{ route('hrd.jobs.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('hrd.jobs.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-briefcase w-5 mr-3"></i>
                Lowongan Kerja
            </a>

            <a href="{{ route('hrd.applications.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('hrd.applications.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-file-alt w-5 mr-3"></i>
                Aplikasi Pelamar
            </a>

            <div class="px-3 py-2 mt-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sistem Penilaian</p>
            </div>

            <a href="{{ route('hrd.evaluations.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('hrd.evaluations.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-star w-5 mr-3"></i>
                Penilaian Pelamar
            </a>

            <a href="{{ route('hrd.rankings.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('hrd.rankings.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-trophy w-5 mr-3"></i>
                Ranking Hasil SAW
            </a>

            <div class="px-3 py-2 mt-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Interview</p>
            </div>

            <a href="{{ route('hrd.interviews.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('hrd.interviews.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-video w-5 mr-3"></i>
                Manajemen Interview
            </a>

            <a href="{{ route('hrd.reports.index') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('hrd.reports.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-chart-bar w-5 mr-3"></i>
                Laporan & Statistik
            </a>

        @elseif(Auth::user()->role === 'applicant')
            <div class="px-3 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menu Pelamar</p>
            </div>

            <a href="{{ route('applicant.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('applicant.dashboard') ? 'bg-purple-50 text-purple-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                Dashboard Saya
            </a>

            <a href="{{ route('applicant.profile') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('applicant.profile') ? 'bg-purple-50 text-purple-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-user-circle w-5 mr-3"></i>
                Profil & CV
            </a>

            <a href="{{ route('applicant.interviews') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('applicant.interviews') ? 'bg-purple-50 text-purple-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-calendar-alt w-5 mr-3"></i>
                Jadwal Interview
            </a>

            <a href="{{ route('applicant.evaluation_results') }}" class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold transition duration-200 {{ request()->routeIs('applicant.evaluation_results') ? 'bg-purple-50 text-purple-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fas fa-trophy w-5 mr-3"></i>
                Hasil Seleksi SAW
            </a>
        @endif
    </nav>
</div>
