<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Notifikasi Email (SMTP)') }}</h2></x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">{{ session('error') }}</div>@endif

            <div class="bg-white rounded-2xl border p-8 mb-6">
                <form method="POST" action="{{ route('admin.email_settings.update') }}" class="space-y-6">
                    @csrf
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="mail_enabled" value="1" {{ ($settings['mail_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="rounded text-blue-600">
                        <span class="font-semibold text-gray-900">Aktifkan pengiriman email via SMTP</span>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">SMTP Host</label>
                            <input type="text" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}" placeholder="smtp.gmail.com" class="w-full px-4 py-3 border rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">SMTP Port</label>
                            <input type="number" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port'] ?? '587') }}" class="w-full px-4 py-3 border rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                            <input type="text" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}" class="w-full px-4 py-3 border rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                            <input type="password" name="smtp_password" placeholder="Kosongkan jika tidak diubah" class="w-full px-4 py-3 border rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Enkripsi</label>
                            <select name="smtp_encryption" class="w-full px-4 py-3 border rounded-xl">
                                <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ ($settings['smtp_encryption'] ?? '') === '' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">From Address</label>
                            <input type="email" name="smtp_from_address" value="{{ old('smtp_from_address', $settings['smtp_from_address'] ?? '') }}" class="w-full px-4 py-3 border rounded-xl">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">From Name</label>
                        <input type="text" name="smtp_from_name" value="{{ old('smtp_from_name', $settings['smtp_from_name'] ?? '') }}" class="w-full px-4 py-3 border rounded-xl">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl"><i class="fas fa-save mr-2"></i>Simpan Konfigurasi SMTP</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl border p-8">
                <h3 class="font-bold text-gray-900 mb-4">Kirim Email Test</h3>
                <form method="POST" action="{{ route('admin.email_settings.test') }}" class="flex gap-4">
                    @csrf
                    <input type="email" name="test_email" required placeholder="email@example.com" class="flex-1 px-4 py-3 border rounded-xl">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 rounded-xl whitespace-nowrap"><i class="fas fa-paper-plane mr-2"></i>Kirim Test</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
