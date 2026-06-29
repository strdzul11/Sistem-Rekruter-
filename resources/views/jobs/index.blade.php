<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lowongan Kerja - {{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</title>
    <link rel="icon" type="image/png" href="{{ asset($settings['company_logo'] ?? 'logo.png') }}">
    <meta name="description" content="Temukan peluang karir terbaik di {{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}. Lamar langsung tanpa perlu daftar akun!">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .job-card { opacity: 0; transform: translateY(30px); transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        .job-card.visible { opacity: 1; transform: translateY(0); }
        .job-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .job-card * { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        @keyframes pulse-glow { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.7; transform: scale(1.1); } }
        .animate-pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }
        .shine-effect { position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); transform: translateX(-100%); }
        .job-card:hover .shine-effect { animation: shine 0.8s ease-out; }
        @keyframes shine { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('landing') }}" class="flex-shrink-0 flex items-center">
                        <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="{{ $settings['company_name'] ?? '' }}" class="w-8 h-8 object-contain">
                        <h1 class="ml-3 text-xl font-bold text-gray-900">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</h1>
                    </a>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('landing') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-home mr-1"></i>Beranda
                    </a>
                    <a href="{{ route('jobs.index') }}" class="text-blue-600 bg-blue-50 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-briefcase mr-1"></i>Lowongan Kerja
                    </a>
                    <a href="{{ route('jobs.checkStatus') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-search mr-1"></i>Cek Status
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-1"></i>Masuk
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm font-medium">
                            <i class="fas fa-user-plus mr-1"></i>Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Bergabunglah dengan Tim Kami</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Temukan kesempatan karir yang tepat — lamar <strong>langsung tanpa perlu registrasi</strong> atau masuk ke akun Anda terlebih dahulu.</p>
            <div class="mt-6 flex flex-wrap gap-3 justify-center">
                <span class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-sm text-sm text-gray-700">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i>Lamar Langsung (Tanpa Akun)
                </span>
                <span class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-sm text-sm text-gray-700">
                    <i class="fas fa-user-check text-green-500 mr-2"></i>Login & Lamar (Pantau Status)
                </span>
            </div>
        </div>
    </div>

    <!-- Jobs Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if($jobs->isEmpty())
            <div class="text-center py-20">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-briefcase text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Lowongan</h3>
                <p class="text-gray-600 mb-6">Saat ini belum ada lowongan kerja yang tersedia. Silakan cek kembali nanti.</p>
                <a href="{{ route('landing') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Kembali ke Beranda</a>
            </div>
        @else
            <div class="mb-6 text-gray-600 text-sm">
                <i class="fas fa-list-ul mr-1"></i> Menampilkan <strong>{{ $jobs->count() }}</strong> lowongan aktif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($jobs as $index => $job)
                    <div class="job-card bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 group overflow-hidden relative" data-delay="{{ $index * 100 }}">
                        <!-- Shine effect -->
                        <div class="shine-effect"></div>
                        <!-- Background gradient on hover -->
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-indigo-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                        <div class="relative p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="transform group-hover:translate-x-1 transition-transform duration-300">
                                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $job->position }}</h3>
                                    <p class="text-sm text-gray-600">{{ $job->company }}</p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full transform group-hover:scale-110 transition-transform shadow-sm">
                                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse-glow"></span>Aktif
                                </span>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600 group-hover:translate-x-1 transition-transform">
                                    <i class="fas fa-map-marker-alt mr-3 text-blue-500"></i>{{ $job->location }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600 group-hover:translate-x-1 transition-transform">
                                    <i class="fas fa-briefcase mr-3 text-green-500"></i>{{ ucfirst($job->employment_type) }}
                                </div>
                                @if(!empty($job->salary_range))
                                    <div class="flex items-center text-sm text-gray-600 group-hover:translate-x-1 transition-transform">
                                        <i class="fas fa-money-bill-wave mr-3 text-yellow-500"></i>{{ $job->salary_range }}
                                    </div>
                                @endif
                            </div>

                            <p class="text-sm text-gray-700 mb-4 line-clamp-2">{{ Str::limit($job->description, 120) }}</p>

                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">{{ $job->created_at->diffForHumans() }}</span>
                                <a href="{{ route('jobs.show', $job) }}" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all text-sm font-medium transform hover:scale-105 hover:shadow-lg">
                                    <i class="fas fa-paper-plane mr-1"></i>Lamar Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="" class="w-8 h-8 object-contain">
                    <span class="ml-3 font-bold text-lg">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</span>
                </div>
                <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} {{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.job-card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const delay = entry.target.getAttribute('data-delay') || 0;
                        setTimeout(() => entry.target.classList.add('visible'), delay);
                    }
                });
            }, { threshold: 0.1 });
            cards.forEach(card => observer.observe(card));
        });
    </script>
</body>
</html>
