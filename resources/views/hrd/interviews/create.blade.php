<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tawarkan Jadwal Interview (Self-Schedule)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Buat Pilihan Jadwal</h3>
                        <p class="text-sm text-gray-500">Tentukan beberapa opsi jadwal agar kandidat dapat memilih waktu terbaik mereka.</p>
                    </div>
                    <a href="{{ route('hrd.interviews.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <strong>Terjadi Kesalahan Validasi</strong>
                        </div>
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('hrd.interviews.store') }}" class="space-y-6" x-data="{
                    slots: [
                        { date: '', time: '' },
                        { date: '', time: '' }
                    ],
                    addSlot() {
                        this.slots.push({ date: '', time: '' });
                    },
                    removeSlot(index) {
                        if (this.slots.length > 2) {
                            this.slots.splice(index, 1);
                        } else {
                            alert('Minimal harus menawarkan 2 opsi jadwal.');
                        }
                    }
                }">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Pilih Pelamar --}}
                        <div class="space-y-2">
                            <label for="application_id" class="block text-sm font-semibold text-gray-700">Pelamar / Lamaran Kerja</label>
                            @if($application)
                                <input type="hidden" name="application_id" value="{{ $application->id }}">
                                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-850">
                                    <div class="font-bold text-gray-900">{{ $application->applicant_name ?? ($application->user?->name ?? '-') }}</div>
                                    <div class="text-xs text-gray-500">Lowongan: {{ $application->jobListing->position }} ({{ $application->application_type }})</div>
                                </div>
                            @else
                                <select id="application_id" name="application_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                                    <option value="">Pilih Pelamar...</option>
                                    @foreach($applications as $app)
                                        <option value="{{ $app->id }}" {{ old('application_id') == $app->id ? 'selected' : '' }}>
                                            {{ $app->applicant_name ?? ($app->user?->name ?? '-') }} — {{ $app->jobListing->position }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <x-input-error :messages="$errors->get('application_id')" class="mt-1" />
                        </div>

                        {{-- Tipe Interview --}}
                        <div class="space-y-2">
                            <label for="interview_type" class="block text-sm font-semibold text-gray-700">Tipe Interview</label>
                            <select id="interview_type" name="interview_type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                                <option value="video" {{ old('interview_type', 'video') === 'video' ? 'selected' : '' }}>Video Call / Online Meeting</option>
                                <option value="phone" {{ old('interview_type') === 'phone' ? 'selected' : '' }}>Telepon / Phone Interview</option>
                                <option value="in-person" {{ old('interview_type') === 'in-person' ? 'selected' : '' }}>In-person / Tatap Muka</option>
                                <option value="online" {{ old('interview_type') === 'online' ? 'selected' : '' }}>Online Test / Lainnya</option>
                            </select>
                            <x-input-error :messages="$errors->get('interview_type')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Durasi --}}
                        <div class="space-y-2">
                            <label for="duration_minutes" class="block text-sm font-semibold text-gray-700">Durasi (Menit)</label>
                            <input type="number" id="duration_minutes" name="duration_minutes" min="15" max="480"
                                   value="{{ old('duration_minutes', 60) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                            <x-input-error :messages="$errors->get('duration_minutes')" class="mt-1" />
                        </div>

                        {{-- Catatan / Instruksi --}}
                        <div class="space-y-2">
                            <label for="notes" class="block text-sm font-semibold text-gray-700">Catatan Tambahan untuk Pelamar</label>
                            <input type="text" id="notes" name="notes" value="{{ old('notes') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                   placeholder="Contoh: Siapkan CV fisik & berpakaian rapi, link meet menyusul">
                            <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Section Repeater Slot Opsi Tanggal/Jam --}}
                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 space-y-4">
                        <div class="flex justify-between items-center">
                            <h4 class="font-bold text-gray-900 text-sm"><i class="fas fa-calendar-alt mr-1 text-emerald-600"></i> Pilihan Opsi Slot Waktu</h4>
                            <span class="text-xs text-gray-400">Minimal 2 opsi slot waktu wajib diisi</span>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(slot, index) in slots" :key="index">
                                <div class="flex flex-col sm:flex-row gap-3 items-end sm:items-center bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative">
                                    <div class="flex-1 w-full space-y-1">
                                        <label class="block text-xs font-semibold text-gray-500">Opsi Tanggal</label>
                                        <input type="date" :name="`slots[${index}][date]`" x-model="slot.date" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    </div>
                                    <div class="flex-1 w-full space-y-1">
                                        <label class="block text-xs font-semibold text-gray-500">Opsi Jam</label>
                                        <input type="time" :name="`slots[${index}][time]`" x-model="slot.time" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                    </div>
                                    <div class="sm:pt-5">
                                        <button type="button" @click="removeSlot(index)"
                                                class="text-red-500 hover:text-red-700 font-semibold text-sm px-3 py-2 bg-red-50 hover:bg-red-100 rounded-lg transition duration-150">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div>
                            <button type="button" @click="addSlot()"
                                    class="inline-flex items-center text-emerald-600 hover:text-emerald-800 font-bold text-sm px-4 py-2 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition duration-150 shadow-sm border border-emerald-100">
                                <i class="fas fa-plus mr-1"></i> Tambah Opsi Jadwal
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition duration-150">
                            <i class="fas fa-paper-plane mr-2"></i> Kirim Opsi Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
