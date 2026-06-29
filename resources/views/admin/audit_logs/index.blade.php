<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Audit Log') }}</h2></x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border p-8">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi / user..." class="px-4 py-2 border rounded-xl text-sm">
                    <select name="module" class="px-4 py-2 border rounded-xl text-sm">
                        <option value="">Semua Modul</option>
                        @foreach($modules as $m)<option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>{{ $m }}</option>@endforeach
                    </select>
                    <select name="action" class="px-4 py-2 border rounded-xl text-sm">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $a)<option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>@endforeach
                    </select>
                    <button type="submit" class="bg-blue-600 text-white rounded-xl font-semibold text-sm">Filter</button>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="text-gray-400 text-xs uppercase border-b">
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">User</th>
                                <th class="py-3 px-4">Modul</th>
                                <th class="py-3 px-4">Aksi</th>
                                <th class="py-3 px-4">Deskripsi</th>
                                <th class="py-3 px-4">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($logs as $log)
                                <tr>
                                    <td class="py-3 px-4 text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4 font-semibold">{{ $log->user?->name ?? '-' }}</td>
                                    <td class="py-3 px-4"><span class="bg-gray-100 px-2 py-0.5 rounded text-xs">{{ $log->module }}</span></td>
                                    <td class="py-3 px-4"><span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs uppercase">{{ $log->action }}</span></td>
                                    <td class="py-3 px-4 text-gray-600">{{ $log->description }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-400">{{ $log->ip_address }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada log aktivitas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
