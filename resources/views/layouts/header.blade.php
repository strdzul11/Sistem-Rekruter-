<header class="w-full h-16 bg-white border-b border-gray-100 flex items-center justify-between px-8 shrink-0">
    <!-- Left: Branding / Page Indicator -->
    <div class="flex items-center">
        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full uppercase tracking-wider">
            {{ Auth::user()->role }} Panel
        </span>
    </div>

    <!-- Right: User Dropdown -->
    <div class="flex items-center space-x-4">
        <!-- User Dropdown Container -->
        <div x-data="{ open: false }" class="relative">
            <!-- Dropdown Trigger Button -->
            <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none group">
                <div class="w-9 h-9 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm group-hover:bg-blue-200 transition duration-200">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="text-left hidden md:block">
                    <p class="text-sm font-bold text-gray-900 leading-tight group-hover:text-blue-600 transition duration-200">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mt-0.5">{{ Auth::user()->role }}</p>
                </div>
                <i class="fas fa-chevron-down text-xs text-gray-400 group-hover:text-gray-600 transition duration-200"></i>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" @click.outside="open = false" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-3 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50 overflow-hidden"
                 style="display: none;">
                
                <div class="px-4 py-2 border-b border-gray-50 bg-gray-50/50">
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Akun Anda</p>
                    <p class="text-sm font-bold text-gray-800 truncate">{{ Auth::user()->email }}</p>
                </div>

                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition duration-150">
                    <i class="fas fa-cog w-4 mr-3 text-gray-400"></i>
                    Pengaturan Akun
                </a>

                <div class="border-t border-gray-100"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition duration-150">
                        <i class="fas fa-sign-out-alt w-4 mr-3"></i>
                        Keluar Aplikasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
