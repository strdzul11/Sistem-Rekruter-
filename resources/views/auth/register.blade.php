<x-guest-layout>
    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Daftar Akun Baru</h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-user mr-2 text-blue-500"></i>Nama Lengkap
            </label>
            <input id="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap Anda" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-envelope mr-2 text-blue-500"></i>Email
            </label>
            <input id="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Masukkan email Anda" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-lock mr-2 text-blue-500"></i>Password
            </label>
            <input id="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="Buat password baru" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-shield-alt mr-2 text-blue-500"></i>Konfirmasi Password
            </label>
            <input id="password_confirmation" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password Anda" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 font-semibold shadow-md mt-2">
            <i class="fas fa-user-plus mr-2"></i>Daftar Akun
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-gray-600 text-sm">Sudah punya akun? 
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-bold transition">
                Masuk Sekarang
            </a>
        </p>
    </div>
</x-guest-layout>
