<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Jadwal Wawancara (HRD)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

            {{-- Card Informasi Interview --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="flex justify-between items-start border-b border-gray-100 pb-6 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Informasi Interview</h3>
                        <p class="text-sm text-gray-500">Detail jadwal dan lokasi/link wawancara kandidat.</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $interview->status === 'confirmed' ? 'bg-green-50 text-green-700' : ($interview->status === 'cancelled' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
                        {{ $interview->is_proposed ? 'Proposed Option' : $interview->status }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm mb-6">
                    <div>
                        <dt class="text-gray-400 font-semibold mb-1">Pelamar</dt>
                        <dd class="font-bold text-gray-900 text-base">
                            {{ $interview->application->applicant_name ?? ($interview->application->user?->name ?? '-') }}
                        </dd>
                        <dd class="text-xs text-gray-400 font-normal mt-0.5">
                            {{ $interview->application->applicant_email ?? ($interview->application->user?->email ?? '-') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 font-semibold mb-1">Posisi yang Dilamar</dt>
                        <dd class="font-bold text-gray-900 text-base">{{ $interview->application->jobListing->position }}</dd>
                        <dd class="text-xs text-gray-400 font-normal mt-0.5">{{ $interview->application->jobListing->company }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 font-semibold mb-1">Waktu Wawancara</dt>
                        <dd class="font-bold text-gray-900">{{ $interview->interview_date ? $interview->interview_date->format('d M Y, H:i') : '-' }} WIB</dd>
                        <dd class="text-xs text-gray-400 mt-0.5">Durasi: {{ $interview->duration_minutes }} menit</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 font-semibold mb-1">Tipe Interview</dt>
                        <dd class="font-bold text-gray-900 uppercase">{{ $interview->interview_type }}</dd>
                    </div>
                </dl>

                @if($interview->notes)
                    <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
                        <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Catatan Interviewer</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $interview->notes }}</p>
                    </div>
                @endif

                {{-- Meeting Link Section --}}
                @if(in_array($interview->interview_type, ['video', 'online']))
                    <div class="border-t border-gray-100 pt-6">
                        <h4 class="text-sm font-bold text-gray-900 mb-3"><i class="fas fa-video mr-2 text-emerald-500"></i>Link Meeting Video Call</h4>
                        
                        @if($interview->meeting_link)
                            <div class="flex flex-col md:flex-row gap-3 items-center">
                                <div class="relative w-full flex-1">
                                    <input type="text" id="meeting_link_input" readonly value="{{ $interview->meeting_link }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl text-gray-700 text-sm py-2.5 px-4 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                                <div class="flex gap-2 w-full md:w-auto">
                                    <button onclick="copyMeetingLink()" class="flex-1 md:flex-initial bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 px-4 rounded-xl text-sm transition flex items-center justify-center gap-2">
                                        <i class="fas fa-copy"></i> Salin
                                    </button>
                                    <a href="{{ $interview->meeting_link }}" target="_blank" class="flex-1 md:flex-initial bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2">
                                        <i class="fas fa-external-link-alt"></i> Gabung
                                    </a>
                                </div>
                            </div>

                            <form action="{{ route('hrd.interviews.regenerateLink', $interview) }}" method="POST" class="mt-4">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1.5">
                                    <i class="fas fa-sync-alt"></i> Buat Ulang Link Meeting Baru
                                </button>
                            </form>
                        @else
                            <p class="text-sm text-gray-500 bg-amber-50 border border-amber-100 text-amber-700 p-4 rounded-xl">
                                Link meeting akan dibuat secara otomatis saat opsi jadwal ini dipilih oleh kandidat.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Accordion Referensi Pertanyaan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Panduan Pertanyaan Wawancara</h3>
                <p class="text-sm text-gray-500 mb-6">Gunakan referensi pertanyaan berikut sesuai dengan posisi lowongan kerja.</p>

                <div class="space-y-4">
                    @forelse($questions as $type => $group)
                        <details class="group bg-gray-50 rounded-xl border border-gray-150 p-4 [&_summary::-webkit-details-marker]:hidden" @if($loop->first) open @endif>
                            <summary class="flex items-center justify-between cursor-pointer focus:outline-none">
                                <h5 class="font-bold text-gray-900 capitalize text-sm flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                                    Tipe: {{ $type }}
                                    <span class="text-xs font-normal text-gray-400">({{ $group->count() }} Pertanyaan)</span>
                                </h5>
                                <span class="transition duration-200 group-open:-rotate-180">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </span>
                            </summary>
                            <div class="mt-4 space-y-3 text-xs text-gray-700">
                                @foreach($group as $q)
                                    <div class="p-3.5 bg-white rounded-lg shadow-xs border border-gray-100 flex justify-between items-start gap-4">
                                        <p class="font-medium text-gray-800 leading-relaxed">{{ $q->question_text }}</p>
                                        <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xxs font-bold uppercase tracking-wider shrink-0">
                                            {{ $q->difficulty_level }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">Belum ada pertanyaan panduan untuk posisi ini.</p>
                    @endforelse
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-between items-center">
                <a href="{{ route('hrd.interviews.index') }}" class="text-sm text-gray-650 hover:text-gray-900 font-semibold"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                
                {{-- Link Placeholder ke route feedback --}}
                <a href="#" onclick="alert('Fitur pengisian feedback interview akan diimplementasikan pada modul penilaian terpisah.')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl text-sm transition shadow-sm">
                    <i class="fas fa-comment-dots mr-1"></i> Isi Feedback Interview
                </a>
            </div>
        </div>
    </div>

    {{-- Script untuk menyalin link --}}
    @push('scripts')
    <script>
        function copyMeetingLink() {
            var linkInput = document.getElementById('meeting_link_input');
            if (linkInput) {
                navigator.clipboard.writeText(linkInput.value).then(function() {
                    alert('Link meeting berhasil disalin ke papan klip!');
                }, function() {
                    alert('Gagal menyalin link meeting.');
                });
            }
        }
    </script>
    @endpush
</x-app-layout>
