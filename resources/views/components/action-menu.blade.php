{{--
    Komponen Dropdown Aksi (Titik Tiga / Kebab Menu)
    Penggunaan:
        <x-action-menu>
            <x-action-item href="...">Ubah</x-action-item>
            <x-action-item href="..." danger>Hapus</x-action-item>
        </x-action-menu>
--}}
<div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false">
    {{-- Tombol Titik Tiga --}}
    <button type="button"
        @click="open = !open"
        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-gray-200"
        title="Aksi">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="5" r="1.5"/>
            <circle cx="12" cy="12" r="1.5"/>
            <circle cx="12" cy="19" r="1.5"/>
        </svg>
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 z-50 mt-1 w-44 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-gray-200 ring-opacity-60 focus:outline-none"
         style="display: none;">
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>
