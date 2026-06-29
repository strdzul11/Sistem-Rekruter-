<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            @if(in_array(Auth::user()->role, ['admin', 'hrd']))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">
                                    {{ __('Integrasi Google Calendar') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Hubungkan akun Google Anda untuk sinkronisasi jadwal interview otomatis ke kalender pribadi Anda.') }}
                                </p>
                            </header>

                            @php
                                $integration = \App\Models\CalendarIntegration::where('user_id', Auth::id())
                                    ->where('integration_type', 'google')
                                    ->where('is_active', true)
                                    ->first();
                            @endphp

                            <div class="mt-6 space-y-6">
                                @if($integration)
                                    <div class="flex items-center gap-4 p-4 bg-green-50 text-green-700 border border-green-200 rounded-xl">
                                        <i class="fab fa-google text-2xl"></i>
                                        <div>
                                            <p class="font-bold text-sm">Terhubung dengan Google Calendar</p>
                                            <p class="text-xs text-green-600">Terakhir diperbarui: {{ $integration->updated_at ? $integration->updated_at->format('d M Y H:i') : '-' }}</p>
                                        </div>
                                    </div>
                                    <form action="{{ route('calendar.disconnect') }}" method="POST" class="mt-4">
                                        @csrf
                                        <x-danger-button>{{ __('Putuskan Koneksi') }}</x-danger-button>
                                    </form>
                                @else
                                    <div class="flex items-center gap-4 p-4 bg-gray-50 text-gray-700 border border-gray-200 rounded-xl">
                                        <i class="fab fa-google text-2xl text-gray-400"></i>
                                        <div>
                                            <p class="font-bold text-sm">Belum Terhubung</p>
                                            <p class="text-xs text-gray-500">Hubungkan untuk mempermudah pemantauan jadwal interview Anda.</p>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('calendar.connect.google') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Hubungkan Google Calendar') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
