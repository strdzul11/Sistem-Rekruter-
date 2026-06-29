<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jadwal Wawancara Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <p class="font-bold text-sm">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Opsi Penjadwalan (Proposed Slots) --}}
            @if($proposedSlots->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-900">Pilih Jadwal Wawancara</h3>
                        <p class="text-sm text-gray-500">Silakan pilih salah satu opsi slot waktu di bawah ini yang paling sesuai untuk Anda.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($proposedSlots as $slot)
                            <div class="border border-emerald-100 rounded-xl p-5 bg-emerald-50/50 hover:bg-emerald-50 transition duration-150 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 uppercase">
                                            {{ $slot->interview_type }}
                                        </span>
                                        <span class="text-xs text-gray-500">Durasi: {{ $slot->duration_minutes }} Menit</span>
                                    </div>
                                    <div class="font-bold text-gray-900 text-base mb-1">
                                        {{ $slot->interview_date->translatedFormat('l, d F Y') }}
                                    </div>
                                    <div class="text-emerald-700 font-semibold text-lg mb-2">
                                        {{ $slot->interview_date->translatedFormat('H:i') }} ({{ $slot->timezone }})
                                    </div>
                                    <div class="text-sm text-gray-600 mb-4">
                                        Posisi: <strong>{{ $slot->application->jobListing->position }}</strong>
                                        @if($slot->notes)
                                            <div class="mt-2 text-xs bg-white/80 p-2.5 rounded border border-gray-100 italic">
                                                Catatan: {{ $slot->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('applicant.interviews.select', $slot) }}">
                                    @csrf
                                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl text-center text-sm transition duration-150 shadow-sm">
                                        Pilih Jadwal Ini
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Jadwal Terkonfirmasi (Confirmed Slots) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Jadwal Interview Aktif</h3>
                    <p class="text-sm text-gray-500">Pastikan koneksi internet stabil dan hadir 10 menit sebelum waktu wawancara.</p>
                </div>

                @forelse($confirmedSlots as $slot)
                    <div class="border border-gray-100 rounded-xl p-6 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 uppercase">
                                    {{ $slot->interview_type }}
                                </span>
                                <span class="text-xs text-gray-500">Durasi: {{ $slot->duration_minutes }} Menit</span>
                            </div>
                            <h4 class="font-bold text-gray-900 text-lg">
                                {{ $slot->interview_date->translatedFormat('l, d F Y') }} pukul {{ $slot->interview_date->translatedFormat('H:i') }} ({{ $slot->timezone }})
                            </h4>
                            <p class="text-sm text-gray-600">
                                Lowongan: <strong>{{ $slot->application->jobListing->position }}</strong>
                            </p>
                            @if($slot->meeting_link)
                                <div class="text-sm">
                                    Link Meeting: <a href="{{ $slot->meeting_link }}" target="_blank" class="text-emerald-600 hover:underline font-semibold">{{ $slot->meeting_link }}</a>
                                </div>
                            @endif
                            @if($slot->meeting_room)
                                <div class="text-sm text-gray-600">
                                    Ruangan: <strong>{{ $slot->meeting_room }}</strong>
                                </div>
                            @endif
                            @if($slot->notes)
                                <p class="text-xs text-gray-500 italic bg-white p-2 rounded border border-gray-100">Catatan: {{ $slot->notes }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-bold bg-green-50 text-green-800">
                                Dikonfirmasi
                            </span>
                            <span class="text-xs text-gray-400">Pewawancara: {{ $slot->interviewer->name }}</span>
                        </div>
                    </div>
                @empty
                    @if($proposedSlots->isEmpty())
                        <div class="bg-gray-50 border border-gray-100 rounded-xl p-8 text-center text-gray-500">
                            Belum ada jadwal wawancara terdekat untuk Anda saat ini. Kami akan mengirimi Anda email notifikasi jika jadwal sudah ditentukan oleh tim HRD.
                        </div>
                    @endif
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
