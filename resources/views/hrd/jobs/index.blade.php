<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Daftar Lowongan Kerja') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Lowongan Pekerjaan</h3>
                        <p class="text-sm text-gray-500">Kelola posting lowongan yang ditampilkan ke pelamar.</p>
                    </div>
                    <a href="{{ route('hrd.jobs.create') }}" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i> Tambah Lowongan
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Posisi / Perusahaan</th>
                                <th class="py-4 px-6">Lokasi</th>
                                <th class="py-4 px-6">Tipe</th>
                                <th class="py-4 px-6">Gaji</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @forelse ($jobs as $job)
                                <tr>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $job->position }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $job->company }}</div>
                                    </td>
                                    <td class="py-4 px-6">{{ $job->location }}</td>
                                    <td class="py-4 px-6 uppercase text-xs">{{ $job->employment_type }}</td>
                                    <td class="py-4 px-6">{{ $job->salary_range ?? '-' }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium uppercase {{ $job->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $job->status }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right space-x-3">
                                        <a href="{{ route('hrd.jobs.edit', $job) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold"><i class="fas fa-edit mr-1"></i>Ubah</a>
                                        <form method="POST" action="{{ route('hrd.jobs.destroy', $job) }}" class="inline" onsubmit="return confirm('Hapus lowongan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-semibold"><i class="fas fa-trash mr-1"></i>Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada lowongan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
