<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Lowongan') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <form method="POST" action="{{ route('hrd.jobs.store') }}" class="space-y-8">
                    @csrf
                    @include('hrd.jobs._form')
                    <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                        <a href="{{ route('hrd.jobs.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold text-sm"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-xl"><i class="fas fa-save mr-2"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
