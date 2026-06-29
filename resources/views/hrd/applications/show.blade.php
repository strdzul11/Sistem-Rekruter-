<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Detail Lamaran') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Informasi Pelamar</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Nama</dt><dd class="font-semibold text-gray-900">{{ $application->applicant_name ?? ($application->user?->name ?? '-') }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="font-semibold text-gray-900">{{ $application->applicant_email ?? ($application->user?->email ?? '-') }}</dd></div>
                    <div><dt class="text-gray-500">Telepon</dt><dd class="font-semibold text-gray-900">{{ $application->applicant_phone ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Lowongan</dt><dd class="font-semibold text-gray-900">{{ $application->jobListing->position }}</dd></div>
                    <div><dt class="text-gray-500">Tipe Lamaran</dt><dd class="font-semibold text-gray-900 uppercase">{{ $application->application_type }}</dd></div>
                    <div><dt class="text-gray-500">Pengalaman</dt><dd class="font-semibold text-gray-900">{{ $application->work_experience ?? '-' }}</dd></div>
                </dl>

                @if($application->cover_letter)
                    <div class="mt-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Cover Letter</h4>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $application->cover_letter }}</p>
                    </div>
                @endif

                @if($application->resume_path)
                    <div class="mt-6 flex flex-wrap gap-4">
                        <a href="{{ route('hrd.applications.resume', $application) }}" class="inline-flex items-center text-emerald-600 hover:text-emerald-800 font-semibold text-sm">
                            <i class="fas fa-file-download mr-2"></i> Unduh CV / Resume
                        </a>
                        @if($application->generated_letter_path)
                            <a href="{{ route('hrd.applications.downloadLetter', $application) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                <i class="fas fa-file-download mr-2"></i> Unduh Surat Keputusan (PDF)
                            </a>
                        @endif
                    </div>
                @elseif($application->generated_letter_path)
                    <div class="mt-6">
                        <a href="{{ route('hrd.applications.downloadLetter', $application) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold text-sm">
                            <i class="fas fa-file-download mr-2"></i> Unduh Surat Keputusan (PDF)
                        </a>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Ubah Status Lamaran</h3>
                <form method="POST" action="{{ route('hrd.applications.update', $application) }}" class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
                    @csrf
                    @method('PUT')
                    <div class="flex-1 w-full">
                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select id="status" name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            @foreach(['pending' => 'Pending', 'reviewed' => 'Ditinjau', 'interview_scheduled' => 'Interview Dijadwalkan', 'accepted' => 'Diterima', 'rejected' => 'Ditolak'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $application->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-xl">Simpan Status</button>
                </form>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('hrd.applications.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold text-sm"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                <a href="{{ route('hrd.evaluations.show', $application) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold text-sm"><i class="fas fa-star mr-1"></i> Beri Penilaian</a>
            </div>
        </div>
    </div>
</x-app-layout>
