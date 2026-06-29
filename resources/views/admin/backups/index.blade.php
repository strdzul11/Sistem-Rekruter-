<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Backup & Restore') }}</h2></x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>@endif
            @if(session('error'))<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>@endif

            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl mb-6 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Peringatan:</strong> Restore akan menimpa seluruh data database dengan isi backup. Pastikan Anda sudah membuat backup terbaru sebelum restore.
            </div>

            <div class="bg-white rounded-2xl border p-8 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-900">Buat Backup Baru</h3>
                        <p class="text-sm text-gray-500">Export data ke file JSON di server.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.backups.store') }}">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm"><i class="fas fa-download mr-2"></i>Buat Backup</button>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-2xl border p-8">
                <h3 class="font-bold text-gray-900 mb-4">Daftar Backup</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-gray-400 text-xs uppercase border-b"><th class="py-3 text-left">File</th><th class="py-3 text-left">Ukuran</th><th class="py-3 text-left">Dibuat</th><th class="py-3 text-right">Aksi</th></tr></thead>
                        <tbody class="divide-y">
                            @forelse($backups as $backup)
                                <tr>
                                    <td class="py-3 font-mono text-xs">{{ $backup['filename'] }}</td>
                                    <td class="py-3">{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                                    <td class="py-3">{{ $backup['created_at'] }}</td>
                                    <td class="py-3 text-right space-x-2">
                                        <form method="POST" action="{{ route('admin.backups.restore') }}" class="inline" onsubmit="return confirm('Yakin restore dari backup ini? Data saat ini akan ditimpa!')">
                                            @csrf
                                            <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                            <button type="submit" class="text-emerald-600 font-semibold hover:text-emerald-800"><i class="fas fa-undo mr-1"></i>Restore</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.backups.destroy', $backup['filename']) }}" class="inline" onsubmit="return confirm('Hapus file backup ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 font-semibold hover:text-red-800"><i class="fas fa-trash mr-1"></i>Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-gray-400">Belum ada backup. Klik "Buat Backup" untuk memulai.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
