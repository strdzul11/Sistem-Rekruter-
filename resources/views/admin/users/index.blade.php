<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
                    <i class="fas fa-check-circle mr-3 text-lg"></i>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
                    <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                    <p class="font-bold text-sm">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Daftar Pengguna Sistem</h3>
                        <p class="text-sm text-gray-500">Kelola data pengguna terdaftar (Admin, HRD, dan Pelamar).</p>
                    </div>
                    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition duration-200 text-sm">
                        <i class="fas fa-plus mr-2"></i> Tambah Pengguna
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Nama Pengguna</th>
                                <th class="py-4 px-6">Email</th>
                                <th class="py-4 px-6">Role</th>
                                <th class="py-4 px-6">Telepon</th>
                                <th class="py-4 px-6">Terdaftar Pada</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="py-4 px-6 font-bold text-gray-900">{{ $user->name }}</td>
                                    <td class="py-4 px-6 text-gray-600">{{ $user->email }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide {{ $user->role === 'admin' ? 'bg-red-50 text-red-700' : ($user->role === 'hrd' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                            {{ $user->role === 'applicant' ? 'Pelamar' : strtoupper($user->role) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-500">{{ $user->phone ?? '-' }}</td>
                                    <td class="py-4 px-6 text-gray-400 font-normal text-xs">{{ $user->created_at ? $user->created_at->format('Y-m-d H:i') : '-' }}</td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="inline-flex items-center gap-3">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 transition font-semibold">
                                                <i class="fas fa-edit mr-1"></i>Ubah
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus pengguna {{ $user->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 transition font-semibold">
                                                        <i class="fas fa-trash mr-1"></i>Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 px-6 text-center text-gray-500">Belum ada pengguna terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
