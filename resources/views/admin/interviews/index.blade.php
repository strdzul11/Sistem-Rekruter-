<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jadwal Wawancara') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Jadwal Sesi Interview</h3>
                    <p class="text-sm text-gray-500">Kelola janji temu wawancara antara tim interviewer dan pelamar.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="py-4 px-6">Pelamar</th>
                                <th class="py-4 px-6">Pewawancara</th>
                                <th class="py-4 px-6">Tipe & Durasi</th>
                                <th class="py-4 px-6">Waktu / Timezone</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700 text-sm font-medium">
                            @forelse ($interviews as $interview)
                                <tr>
                                    <td class="py-4 px-6 font-bold text-gray-900">
                                        {{ $interview->application->applicant_name ?? ($interview->application->user ? $interview->application->user->name : '-') }}
                                    </td>
                                    <td class="py-4 px-6 text-gray-600">
                                        {{ $interview->interviewer->name }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-semibold uppercase text-xs">{{ $interview->interview_type }}</span>
                                        <div class="text-xs text-gray-400 font-normal mt-0.5">{{ $interview->duration_minutes }} menit</div>
                                    </td>
                                    <td class="py-4 px-6 text-gray-600">
                                        {{ $interview->interview_date }}
                                        <span class="text-xs text-gray-400">({{ $interview->timezone }})</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide bg-blue-50 text-blue-700">
                                            {{ $interview->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <a href="{{ route('admin.interviews.show', $interview) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-xs">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        Belum ada jadwal wawancara yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
