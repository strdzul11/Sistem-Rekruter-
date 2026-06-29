<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="mb-8 border-b border-gray-100 pb-4">
                    <h3 class="text-xl font-bold text-gray-900">Pengguna Baru</h3>
                    <p class="text-sm text-gray-500">Tambahkan akun Admin, HRD, atau Pelamar ke sistem.</p>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-8">
                    @csrf
                    @include('admin.users._form')

                    <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                        <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold text-sm transition">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl shadow-md transition duration-200">
                            <i class="fas fa-save mr-2"></i> Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
