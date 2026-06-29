@props(['user' => null])

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label for="name" class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
            <input type="text" id="name" name="name"
                   value="{{ old('name', $user?->name) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="Masukkan nama lengkap" required>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div class="space-y-2">
            <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email', $user?->email) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="nama@email.com" required>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label for="role" class="block text-sm font-semibold text-gray-700">Role</label>
            <select id="role" name="role"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" required>
                <option value="admin" {{ old('role', $user?->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="hrd" {{ old('role', $user?->role) === 'hrd' ? 'selected' : '' }}>HRD</option>
                <option value="applicant" {{ old('role', $user?->role ?? 'applicant') === 'applicant' ? 'selected' : '' }}>Pelamar</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-1" />
        </div>

        <div class="space-y-2">
            <label for="phone" class="block text-sm font-semibold text-gray-700">Telepon</label>
            <input type="text" id="phone" name="phone"
                   value="{{ old('phone', $user?->phone) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="08xxxxxxxxxx">
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>
    </div>

    <div class="space-y-2">
        <label for="address" class="block text-sm font-semibold text-gray-700">Alamat</label>
        <textarea id="address" name="address" rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                  placeholder="Alamat lengkap (opsional)">{{ old('address', $user?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 border-t border-gray-100">
        <div class="space-y-2">
            <label for="password" class="block text-sm font-semibold text-gray-700">
                Password @if($user)<span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span>@endif
            </label>
            <input type="password" id="password" name="password"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="{{ $user ? 'Password baru' : 'Minimal 8 karakter' }}"
                   {{ $user ? '' : 'required' }}>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="space-y-2">
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="Ulangi password"
                   {{ $user ? '' : 'required' }}>
        </div>
    </div>
</div>
