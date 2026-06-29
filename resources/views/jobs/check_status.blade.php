<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Lamaran - {{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</title>
    <link rel="icon" type="image/png" href="{{ asset($settings['company_logo'] ?? 'logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('landing') }}" class="flex-shrink-0 flex items-center">
                        <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="" class="w-8 h-8 object-contain">
                        <h1 class="ml-3 text-xl font-bold text-gray-900">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</h1>
                    </a>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('landing') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-home mr-1"></i>Beranda
                    </a>
                    <a href="{{ route('jobs.index') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-briefcase mr-1"></i>Lowongan
                    </a>
                    <a href="{{ route('jobs.checkStatus') }}" class="text-blue-600 bg-blue-50 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-search mr-1"></i>Cek Status
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-1">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-search text-blue-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Cek Status Lamaran</h1>
            <p class="text-gray-600">Masukkan email yang Anda gunakan saat melamar untuk melihat status lamaran Anda</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl mb-6 flex items-start">
                <i class="fas fa-check-circle text-green-600 text-xl mr-3 mt-0.5"></i>
                <div>
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl mb-6 flex items-start">
                <i class="fas fa-exclamation-circle text-red-600 text-xl mr-3 mt-0.5"></i>
                <div>
                    <p class="font-semibold">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Search Form -->
        <form method="POST" action="{{ route('jobs.searchStatus') }}" class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            @csrf
            <div class="flex gap-3">
                <div class="flex-1 relative">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="email" name="email" value="{{ $searchEmail ?? '' }}" required
                        class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="Masukkan email Anda">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition font-medium shadow-lg hover:shadow-xl">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
            @if($errors->has('email'))
                <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $errors->first('email') }}</p>
            @endif
        </form>

        <!-- Results -->
        @isset($applications)
            @if($applications->isEmpty())
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak Ditemukan</h3>
                    <p class="text-gray-600 mb-4">Tidak ada lamaran yang terdaftar dengan email <strong>{{ $searchEmail }}</strong>.</p>
                    <a href="{{ route('jobs.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                        <i class="fas fa-arrow-right mr-1"></i>Lihat Lowongan Tersedia
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    <p class="text-sm text-gray-600"><i class="fas fa-list-ul mr-1"></i> Ditemukan <strong>{{ $applications->count() }}</strong> lamaran untuk <strong>{{ $searchEmail }}</strong></p>

                    @foreach($applications as $app)
                        @php
                            $statusConfig = [
                                'pending'             => ['label' => 'Menunggu Review', 'color' => 'yellow', 'icon' => 'clock'],
                                'reviewed'            => ['label' => 'Sedang Ditinjau', 'color' => 'blue',   'icon' => 'eye'],
                                'accepted'            => ['label' => 'Diterima',        'color' => 'green',  'icon' => 'check-circle'],
                                'rejected'            => ['label' => 'Ditolak',         'color' => 'red',    'icon' => 'times-circle'],
                                'interview_scheduled' => ['label' => 'Interview Dijadwalkan', 'color' => 'purple', 'icon' => 'calendar-check'],
                            ];
                            $cfg = $statusConfig[$app->status] ?? $statusConfig['pending'];
                        @endphp
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">{{ $app->jobListing->position ?? 'Posisi Tidak Tersedia' }}</h3>
                                        <p class="text-sm text-gray-500">{{ $app->jobListing->company ?? '-' }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-{{ $cfg['color'] }}-100 text-{{ $cfg['color'] }}-800">
                                        <i class="fas fa-{{ $cfg['icon'] }} mr-1.5"></i>{{ $cfg['label'] }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-4 text-sm text-gray-500 pb-3 border-b border-gray-100">
                                    <span><i class="fas fa-calendar mr-1"></i>Dilamar: {{ $app->created_at->format('d M Y, H:i') }}</span>
                                    <span><i class="fas fa-tag mr-1"></i>{{ $app->application_type === 'quick_apply' ? 'Lamar Langsung' : 'Akun Terdaftar' }}</span>
                                </div>

                                {{-- Opsi Self-Schedule (Proposed Slots) --}}
                                @php
                                    $proposed = $app->interviewSchedules->where('is_proposed', true)->where('status', 'scheduled')->where('interview_date', '>', now());
                                    $confirmed = $app->interviewSchedules->where('is_proposed', false)->where('status', 'confirmed')->first();
                                @endphp

                                @if($app->status === 'interview_scheduled' && $proposed->isNotEmpty())
                                    <div class="mt-4 p-5 bg-purple-50 rounded-xl border border-purple-100">
                                        <h4 class="font-bold text-purple-900 text-sm mb-2"><i class="fas fa-calendar-alt mr-1"></i> Pilih Jadwal Wawancara</h4>
                                        <p class="text-xs text-purple-700 mb-4">Silakan pilih salah satu jadwal interview di bawah ini:</p>
                                        <div class="grid grid-cols-1 gap-3">
                                            @foreach($proposed as $slot)
                                                <div class="bg-white p-4 rounded-xl border border-purple-150 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                                    <div>
                                                        <div class="text-sm font-bold text-gray-900">{{ $slot->interview_date->translatedFormat('l, d F Y') }}</div>
                                                        <div class="text-sm text-purple-700 font-semibold">{{ $slot->interview_date->translatedFormat('H:i') }} ({{ $slot->timezone }})</div>
                                                        <div class="text-xs text-gray-400 mt-0.5">Tipe: <span class="uppercase font-semibold">{{ $slot->interview_type }}</span> | Durasi: {{ $slot->duration_minutes }} Menit</div>
                                                        @if($slot->notes)
                                                            <div class="text-xs text-gray-500 italic mt-1">Catatan: {{ $slot->notes }}</div>
                                                        @endif
                                                    </div>
                                                    @if($app->application_type === 'quick_apply' && $slot->selection_token)
                                                        <form method="POST" action="{{ route('interviews.selectByToken', $slot->selection_token) }}">
                                                            @csrf
                                                            <button type="submit" class="w-full sm:w-auto bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition duration-150 shadow-sm">
                                                                Pilih Slot Ini
                                                            </button>
                                                        </form>
                                                    @elseif($app->application_type === 'registered')
                                                        <a href="{{ route('applicant.interviews') }}" class="w-full sm:w-auto bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg text-xs text-center transition duration-150 shadow-sm">
                                                            Masuk ke Akun Anda
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Jadwal Terkonfirmasi --}}
                                @if($confirmed)
                                    <div class="mt-4 p-5 bg-green-50 rounded-xl border border-green-150">
                                        <h4 class="font-bold text-green-950 text-sm mb-1"><i class="fas fa-check-circle mr-1 text-green-600"></i> Jadwal Interview Dikonfirmasi</h4>
                                        <div class="text-sm text-gray-800 space-y-1">
                                            <div>Hari & Tanggal: <strong>{{ $confirmed->interview_date->translatedFormat('l, d F Y') }}</strong></div>
                                            <div>Waktu: <strong>{{ $confirmed->interview_date->translatedFormat('H:i') }} ({{ $confirmed->timezone }})</strong></div>
                                            <div>Tipe: <span class="uppercase font-semibold text-xs bg-white px-2 py-0.5 rounded border">{{ $confirmed->interview_type }}</span></div>
                                            <div>Durasi: {{ $confirmed->duration_minutes }} Menit</div>
                                            @if($confirmed->meeting_link)
                                                <div>Link Meeting: <a href="{{ $confirmed->meeting_link }}" target="_blank" class="text-green-700 hover:underline font-semibold">{{ $confirmed->meeting_link }}</a></div>
                                            @endif
                                            @if($confirmed->meeting_room)
                                                <div>Ruangan: <strong>{{ $confirmed->meeting_room }}</strong></div>
                                            @endif
                                            @if($confirmed->notes)
                                                <div class="text-xs text-gray-500 italic bg-white/50 p-2 rounded mt-2 border">Catatan: {{ $confirmed->notes }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endisset
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="" class="w-8 h-8 object-contain">
                    <span class="ml-3 font-bold">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</span>
                </div>
                <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} {{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>
</body>
</html>
