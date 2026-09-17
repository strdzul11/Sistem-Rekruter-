<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center shadow-sm">
                    <i class="fas fa-check-circle mr-3 text-lg"></i>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center shadow-sm">
                    <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                    <p class="font-bold text-sm">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Header + Tombol Tambah --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h3 class="text-xl font-extrabold text-gray-900">Manajemen Pengguna Sistem</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Kelola akun berdasarkan peran: Admin, HRD, dan Pelamar.</p>
                </div>
                <a href="{{ route('admin.users.create') }}"
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition text-sm shadow-sm">
                    <i class="fas fa-user-plus"></i> Tambah Pengguna
                </a>
            </div>

            {{-- Stat Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Pengguna</p>
                        <p class="text-2xl font-extrabold text-gray-900">{{ $counts['total'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5 flex items-center gap-4 cursor-pointer hover:border-red-300 transition" onclick="switchTab('admin')">
                    <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                        <i class="fas fa-user-shield text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Admin</p>
                        <p class="text-2xl font-extrabold text-red-600">{{ $counts['admin'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5 flex items-center gap-4 cursor-pointer hover:border-amber-300 transition" onclick="switchTab('hrd')">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500">
                        <i class="fas fa-user-tie text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">HRD</p>
                        <p class="text-2xl font-extrabold text-amber-600">{{ $counts['hrd'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 flex items-center gap-4 cursor-pointer hover:border-blue-300 transition" onclick="switchTab('applicant')">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500">
                        <i class="fas fa-user-graduate text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Pelamar</p>
                        <p class="text-2xl font-extrabold text-blue-600">{{ $counts['applicant'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 flex">
                    <button id="tab-btn-admin"
                        onclick="switchTab('admin')"
                        class="tab-btn flex items-center gap-2 px-6 py-4 text-sm font-semibold border-b-2 transition-colors">
                        <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 text-xs flex items-center justify-center font-bold">{{ $counts['admin'] }}</span>
                        <i class="fas fa-user-shield text-red-500"></i>
                        Administrator
                    </button>
                    <button id="tab-btn-hrd"
                        onclick="switchTab('hrd')"
                        class="tab-btn flex items-center gap-2 px-6 py-4 text-sm font-semibold border-b-2 transition-colors">
                        <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 text-xs flex items-center justify-center font-bold">{{ $counts['hrd'] }}</span>
                        <i class="fas fa-user-tie text-amber-500"></i>
                        HRD
                    </button>
                    <button id="tab-btn-applicant"
                        onclick="switchTab('applicant')"
                        class="tab-btn flex items-center gap-2 px-6 py-4 text-sm font-semibold border-b-2 transition-colors">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs flex items-center justify-center font-bold">{{ $counts['applicant'] }}</span>
                        <i class="fas fa-user-graduate text-blue-500"></i>
                        Pelamar
                    </button>
                </div>

                {{-- ======= TAB: ADMIN ======= --}}
                <div id="tab-admin" class="tab-panel">
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-gray-900">Daftar Administrator</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Memiliki akses penuh ke seluruh sistem.</p>
                        </div>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            <input type="text" id="search-admin" oninput="filterTable('admin')"
                                placeholder="Cari admin..."
                                class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-200 focus:border-red-400 outline-none transition">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left" id="table-admin">
                            <thead>
                                <tr class="bg-red-50 text-xs font-semibold text-red-700 uppercase tracking-wider">
                                    <th class="py-3 px-6">Nama</th>
                                    <th class="py-3 px-6">Email</th>
                                    <th class="py-3 px-6">Telepon</th>
                                    <th class="py-3 px-6">Terdaftar</th>
                                    <th class="py-3 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @forelse($admins as $user)
                                <tr class="hover:bg-red-50/40 transition user-row">
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 font-bold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <span class="font-semibold text-gray-900">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-6 text-gray-500">{{ $user->email }}</td>
                                    <td class="py-3.5 px-6 text-gray-500">{{ $user->phone ?? '-' }}</td>
                                    <td class="py-3.5 px-6 text-gray-400 text-xs">{{ $user->created_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="py-3.5 px-6 text-right">
                                        <x-action-menu>
                                            <x-action-item :href="route('admin.users.edit', $user)" icon="fas fa-edit">Ubah</x-action-item>
                                            @if($user->id !== auth()->id())
                                            <x-action-item method="DELETE" :action="route('admin.users.destroy', $user)" :confirm="'Yakin hapus ' . $user->name . '?'" icon="fas fa-trash" danger>Hapus</x-action-item>
                                            @endif
                                        </x-action-menu>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400">
                                        <i class="fas fa-user-shield text-3xl mb-2 block opacity-30"></i>
                                        Belum ada admin terdaftar.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ======= TAB: HRD ======= --}}
                <div id="tab-hrd" class="tab-panel hidden">
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-gray-900">Daftar Tim HRD</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Mengelola lowongan, lamaran, dan evaluasi pelamar.</p>
                        </div>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            <input type="text" id="search-hrd" oninput="filterTable('hrd')"
                                placeholder="Cari HRD..."
                                class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none transition">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left" id="table-hrd">
                            <thead>
                                <tr class="bg-amber-50 text-xs font-semibold text-amber-700 uppercase tracking-wider">
                                    <th class="py-3 px-6">Nama</th>
                                    <th class="py-3 px-6">Email</th>
                                    <th class="py-3 px-6">Telepon</th>
                                    <th class="py-3 px-6">Terdaftar</th>
                                    <th class="py-3 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @forelse($hrds as $user)
                                <tr class="hover:bg-amber-50/40 transition user-row">
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <span class="font-semibold text-gray-900">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-6 text-gray-500">{{ $user->email }}</td>
                                    <td class="py-3.5 px-6 text-gray-500">{{ $user->phone ?? '-' }}</td>
                                    <td class="py-3.5 px-6 text-gray-400 text-xs">{{ $user->created_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="py-3.5 px-6 text-right">
                                        <x-action-menu>
                                            <x-action-item :href="route('admin.users.edit', $user)" icon="fas fa-edit">Ubah</x-action-item>
                                            <x-action-item method="DELETE" :action="route('admin.users.destroy', $user)" :confirm="'Yakin hapus ' . $user->name . '?'" icon="fas fa-trash" danger>Hapus</x-action-item>
                                        </x-action-menu>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400">
                                        <i class="fas fa-user-tie text-3xl mb-2 block opacity-30"></i>
                                        Belum ada HRD terdaftar.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ======= TAB: PELAMAR ======= --}}
                <div id="tab-applicant" class="tab-panel hidden">
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-gray-900">Daftar Pelamar</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Akun pelamar yang mendaftar melalui portal rekrutmen.</p>
                        </div>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            <input type="text" id="search-applicant" oninput="filterTable('applicant')"
                                placeholder="Cari pelamar..."
                                class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none transition">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left" id="table-applicant">
                            <thead>
                                <tr class="bg-blue-50 text-xs font-semibold text-blue-700 uppercase tracking-wider">
                                    <th class="py-3 px-6">Nama</th>
                                    <th class="py-3 px-6">Email</th>
                                    <th class="py-3 px-6">Telepon</th>
                                    <th class="py-3 px-6">Alamat</th>
                                    <th class="py-3 px-6">Terdaftar</th>
                                    <th class="py-3 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @forelse($applicants as $user)
                                <tr class="hover:bg-blue-50/40 transition user-row">
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <span class="font-semibold text-gray-900">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-6 text-gray-500">{{ $user->email }}</td>
                                    <td class="py-3.5 px-6 text-gray-500">{{ $user->phone ?? '-' }}</td>
                                    <td class="py-3.5 px-6 text-gray-400 text-xs max-w-xs truncate">{{ $user->address ?? '-' }}</td>
                                    <td class="py-3.5 px-6 text-gray-400 text-xs">{{ $user->created_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="py-3.5 px-6 text-right">
                                        <x-action-menu>
                                            <x-action-item :href="route('admin.users.edit', $user)" icon="fas fa-edit">Ubah</x-action-item>
                                            <x-action-item method="DELETE" :action="route('admin.users.destroy', $user)" :confirm="'Yakin hapus ' . $user->name . '?'" icon="fas fa-trash" danger>Hapus</x-action-item>
                                        </x-action-menu>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-gray-400">
                                        <i class="fas fa-user-graduate text-3xl mb-2 block opacity-30"></i>
                                        Belum ada pelamar terdaftar.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>{{-- end card --}}
        </div>
    </div>

    @push('scripts')
    <script>
        const TAB_COLORS = {
            admin:     { active: 'border-red-500 text-red-600',    inactive: 'border-transparent text-gray-400 hover:text-gray-700' },
            hrd:       { active: 'border-amber-500 text-amber-600', inactive: 'border-transparent text-gray-400 hover:text-gray-700' },
            applicant: { active: 'border-blue-500 text-blue-600',  inactive: 'border-transparent text-gray-400 hover:text-gray-700' },
        };

        function switchTab(role) {
            // Sembunyikan semua panel
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));

            // Reset semua tombol tab
            ['admin', 'hrd', 'applicant'].forEach(r => {
                const btn = document.getElementById('tab-btn-' + r);
                btn.className = btn.className
                    .replace(TAB_COLORS[r].active, '')
                    .replace(TAB_COLORS[r].inactive, '');
                btn.classList.add(...TAB_COLORS[r].inactive.split(' '));
            });

            // Aktifkan panel & tab yang dipilih
            document.getElementById('tab-' + role).classList.remove('hidden');
            const activeBtn = document.getElementById('tab-btn-' + role);
            activeBtn.className = activeBtn.className
                .replace(TAB_COLORS[role].inactive, '');
            activeBtn.classList.add(...TAB_COLORS[role].active.split(' '));

            // Simpan di URL hash
            history.replaceState(null, '', '#' + role);
        }

        function filterTable(role) {
            const query = document.getElementById('search-' + role).value.toLowerCase();
            const rows  = document.querySelectorAll('#table-' + role + ' .user-row');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }

        // Inisialisasi: baca hash URL atau default ke tab pertama
        document.addEventListener('DOMContentLoaded', function () {
            const hash = window.location.hash.replace('#', '');
            const valid = ['admin', 'hrd', 'applicant'];
            switchTab(valid.includes(hash) ? hash : 'admin');
        });
    </script>
    @endpush
</x-app-layout>
