<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Hak Akses Role') }}</h2></x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">{{ session('success') }}</div>@endif

            <div class="bg-white rounded-2xl border p-8">
                <p class="text-sm text-gray-500 mb-6">Atur hak akses granular per fitur untuk role HRD dan Pelamar. Admin selalu memiliki akses penuh.</p>

                <form method="POST" action="{{ route('admin.permissions.update') }}">
                    @csrf @method('PUT')
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-xs uppercase text-gray-400">
                                    <th class="py-3 text-left">Fitur</th>
                                    <th class="py-3 text-center">HRD</th>
                                    <th class="py-3 text-center">Pelamar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($matrix as $key => $row)
                                    <tr>
                                        <td class="py-3">
                                            <div class="font-semibold text-gray-900">{{ $row['label'] }}</div>
                                            <div class="text-xs text-gray-400 font-mono">{{ $key }}</div>
                                        </td>
                                        <td class="py-3 text-center">
                                            @if(array_key_exists('hrd', $row))
                                                <input type="checkbox" name="permissions[hrd][{{ $key }}]" value="1" {{ $row['hrd'] ? 'checked' : '' }} class="rounded text-blue-600">
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center">
                                            @if(array_key_exists('applicant', $row))
                                                <input type="checkbox" name="permissions[applicant][{{ $key }}]" value="1" {{ $row['applicant'] ? 'checked' : '' }} class="rounded text-blue-600">
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl"><i class="fas fa-save mr-2"></i>Simpan Hak Akses</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
