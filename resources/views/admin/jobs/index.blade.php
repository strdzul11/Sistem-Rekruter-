<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Lowongan Pekerjaan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Daftar Lowongan Pekerjaan</h3>
                        <p class="text-sm text-gray-500">Kelola posting lowongan, persyaratan, dan tipe penugasan kerja.</p>
                    </div>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition duration-200 text-sm">
                        + Lowongan Baru
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Posisi / Perusahaan</th>
                                <th class="py-4 px-6">Lokasi</th>
                                <th class="py-4 px-6">Tipe Pekerjaan</th>
                                <th class="py-4 px-6">Gaji</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @foreach ($jobs as $job)
                                <tr>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $job->position }}</div>
                                        <div class="text-xs text-gray-400 font-normal mt-0.5">{{ $job->company }}</div>
                                    </td>
                                    <td class="py-4 px-6 text-gray-600">{{ $job->location }}</td>
                                    <td class="py-4 px-6 uppercase text-xs tracking-wider text-gray-500">{{ $job->employment_type }}</td>
                                    <td class="py-4 px-6 text-gray-900 font-semibold">{{ $job->salary_range ?? '-' }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide {{ $job->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $job->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <x-action-menu>
                                            <x-action-item icon="fas fa-edit">Ubah</x-action-item>
                                            <x-action-item icon="fas fa-trash" danger>Hapus</x-action-item>
                                        </x-action-menu>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
