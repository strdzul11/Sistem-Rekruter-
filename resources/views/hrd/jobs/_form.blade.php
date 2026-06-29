@props(['job' => null])

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label for="position" class="block text-sm font-semibold text-gray-700">Posisi / Jabatan</label>
            <input type="text" id="position" name="position"
                   value="{{ old('position', $job?->position) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                   placeholder="Contoh: Software Engineer" required>
            <x-input-error :messages="$errors->get('position')" class="mt-1" />
        </div>

        <div class="space-y-2">
            <label for="location" class="block text-sm font-semibold text-gray-700">Lokasi</label>
            <input type="text" id="location" name="location"
                   value="{{ old('location', $job?->location) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                   placeholder="Contoh: Jakarta" required>
            <x-input-error :messages="$errors->get('location')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-2">
            <label for="employment_type" class="block text-sm font-semibold text-gray-700">Tipe Pekerjaan</label>
            <select id="employment_type" name="employment_type"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                @foreach(['full-time' => 'Full-time', 'part-time' => 'Part-time', 'contract' => 'Kontrak', 'internship' => 'Magang'] as $value => $label)
                    <option value="{{ $value }}" {{ old('employment_type', $job?->employment_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('employment_type')" class="mt-1" />
        </div>

        <div class="space-y-2">
            <label for="salary_range" class="block text-sm font-semibold text-gray-700">Rentang Gaji</label>
            <input type="text" id="salary_range" name="salary_range"
                   value="{{ old('salary_range', $job?->salary_range) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                   placeholder="Rp 5.000.000 - Rp 8.000.000">
            <x-input-error :messages="$errors->get('salary_range')" class="mt-1" />
        </div>

        <div class="space-y-2">
            <label for="status" class="block text-sm font-semibold text-gray-700">Status</label>
            <select id="status" name="status"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                @foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'closed' => 'Ditutup'] as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $job?->status ?? 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-1" />
        </div>
    </div>

    {{-- Fitur #6: Deadline & Kuota --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label for="application_deadline" class="block text-sm font-semibold text-gray-700">Batas Tanggal Pendaftaran</label>
            <input type="date" id="application_deadline" name="application_deadline"
                   value="{{ old('application_deadline', $job?->application_deadline?->format('Y-m-d')) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <p class="text-xs text-gray-400">Opsional. Lowongan akan otomatis ditutup setelah tanggal ini.</p>
            <x-input-error :messages="$errors->get('application_deadline')" class="mt-1" />
        </div>

        <div class="space-y-2">
            <label for="applicant_quota" class="block text-sm font-semibold text-gray-700">Kuota Pelamar</label>
            <input type="number" id="applicant_quota" name="applicant_quota" min="1"
                   value="{{ old('applicant_quota', $job?->applicant_quota) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                   placeholder="Kosongkan jika tanpa batas">
            <p class="text-xs text-gray-400">Opsional. Lowongan ditutup otomatis saat kuota terpenuhi.</p>
            <x-input-error :messages="$errors->get('applicant_quota')" class="mt-1" />
        </div>
    </div>

    <div class="space-y-2">
        <label for="description" class="block text-sm font-semibold text-gray-700">Deskripsi Pekerjaan</label>
        <textarea id="description" name="description" rows="4"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  placeholder="Jelaskan tanggung jawab dan lingkup pekerjaan" required>{{ old('description', $job?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    <div class="space-y-2">
        <label for="requirements" class="block text-sm font-semibold text-gray-700">Persyaratan</label>
        <textarea id="requirements" name="requirements" rows="4"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  placeholder="Sebutkan kualifikasi dan persyaratan pelamar" required>{{ old('requirements', $job?->requirements) }}</textarea>
        <x-input-error :messages="$errors->get('requirements')" class="mt-1" />
    </div>
</div>
