<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kriteria Penilaian SAW') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Daftar Kriteria SAW</h3>
                        <p class="text-sm text-gray-500">Bobot total seluruh kriteria idealnya bernilai 100%.</p>
                    </div>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition duration-200 text-sm">
                        + Tambah Kriteria
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Nama Kriteria</th>
                                <th class="py-4 px-6">Tipe</th>
                                <th class="py-4 px-6">Bobot (%)</th>
                                <th class="py-4 px-6">Rentang Nilai</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @foreach ($criteria as $c)
                                <tr>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $c->name }}</div>
                                        <div class="text-xs text-gray-400 font-normal mt-0.5">{{ $c->description }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide {{ $c->type === 'benefit' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                            {{ $c->type }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-900 font-semibold">{{ $c->weight }}%</td>
                                    <td class="py-4 px-6 text-gray-500">{{ $c->min_value }} - {{ $c->max_value }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $c->is_active ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $c->is_active ? 'Aktif' : 'Non-aktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <button class="text-blue-600 hover:text-blue-800 transition">Ubah</button>
                                        <button class="text-red-600 hover:text-red-800 transition">Hapus</button>
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
