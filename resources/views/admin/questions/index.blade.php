<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Template Pertanyaan Wawancara') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Pertanyaan Templat</h3>
                    <p class="text-sm text-gray-500">Daftar templat pertanyaan interview pembawa acuan.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Teks Pertanyaan</th>
                                <th class="py-4 px-6">Tipe Pertanyaan</th>
                                <th class="py-4 px-6">Tingkat Kesulitan</th>
                                <th class="py-4 px-6">Posisi Relevan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @foreach ($questions as $q)
                                <tr>
                                    <td class="py-4 px-6 text-gray-900">{{ $q->question_text }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide bg-purple-50 text-purple-700">
                                            {{ $q->question_type }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 uppercase text-xs text-gray-500">{{ $q->difficulty_level }}</td>
                                    <td class="py-4 px-6 text-gray-400">{{ $q->job_position ?? 'Semua Posisi' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
