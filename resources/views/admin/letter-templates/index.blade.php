<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Konfigurasi Template Surat') }}
        </h2>
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
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Daftar Template Surat</h3>
                        <p class="text-sm text-gray-500">Kelola email dan dokumen penawaran otomatis untuk kandidat.</p>
                    </div>
                    <a href="{{ route('admin.letter-templates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition duration-200 text-sm">
                        + Tambah Template
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Nama Template</th>
                                <th class="py-4 px-6">Tipe</th>
                                <th class="py-4 px-6">Subjek</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @forelse ($templates as $t)
                                <tr>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $t->name }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        @php
                                            $typeColors = [
                                                'acceptance' => 'bg-green-50 text-green-700',
                                                'rejection' => 'bg-red-50 text-red-700',
                                                'interview_invitation' => 'bg-purple-50 text-purple-700',
                                            ];
                                            $typeLabels = [
                                                'acceptance' => 'Penerimaan',
                                                'rejection' => 'Penolakan',
                                                'interview_invitation' => 'Undangan Interview',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase {{ $typeColors[$t->type] ?? 'bg-gray-50 text-gray-700' }}">
                                            {{ $typeLabels[$t->type] ?? $t->type }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-500 max-w-xs truncate">{{ $t->subject }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $t->is_active ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $t->is_active ? 'Aktif' : 'Non-aktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <x-action-menu>
                                            <x-action-item :href="route('admin.letter-templates.edit', $t)" icon="fas fa-edit">Ubah</x-action-item>
                                            <x-action-item method="DELETE" :action="route('admin.letter-templates.destroy', $t)" confirm="Apakah Anda yakin ingin menghapus template ini?" icon="fas fa-trash" danger>Hapus</x-action-item>
                                        </x-action-menu>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">Belum ada template surat. Silakan buat template baru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
