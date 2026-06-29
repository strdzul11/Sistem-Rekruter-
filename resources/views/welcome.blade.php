<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }} - {{ $settings['landing_hero_title'] ?? 'Bergabunglah dengan Tim Kami' }}</title>
    <link rel="icon" type="image/png" href="{{ asset($settings['company_logo'] ?? 'logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Enhanced hover effects */
        .job-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Smooth transitions for all elements */
        .job-card * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Pulse animation for status indicator */
        @keyframes pulse-glow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }
        
        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        /* Button hover effect */
        .group\/btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
        }
        
        /* Shine effect animation */
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .job-card:hover .shine-effect {
            animation: shine 0.8s ease-out;
        }
        
        /* Custom animation delays for staggered effect */
        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }
        .animate-delay-500 { animation-delay: 0.5s; }
        .animate-delay-600 { animation-delay: 0.6s; }
        
        /* Hide cards initially until they come into view */
        .job-card {
            opacity: 0;
            transform: translateY(30px);
        }
        
        /* Show cards when animation class is added */
        .job-card.animate__animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Force animation to run */
        .job-card.animate__animated.animate__zoomInLeft,
        .job-card.animate__animated.animate__zoomInUp,
        .job-card.animate__animated.animate__zoomInRight {
            animation-fill-mode: both;
            animation-duration: 0.8s;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}" class="w-8 h-8 object-contain">
                        <h1 class="ml-3 text-xl font-bold text-gray-900">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('jobs.index') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-briefcase mr-2"></i>Lowongan Kerja
                    </a>
                    <a href="{{ route('jobs.checkStatus') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-search mr-2"></i>Cek Status
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm font-medium">
                            <i class="fas fa-user-plus mr-2"></i>Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                    {{ $settings['landing_hero_title'] ?? 'Bergabunglah dengan Tim Kami' }}
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    {{ $settings['landing_hero_subtitle'] ?? ($settings['company_name'] ?? 'Perusahaan') . ' mencari talenta terbaik untuk bergabung dengan tim food and beverage terdepan kami' }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('jobs.index') }}" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-200 text-lg font-medium">
                        <i class="fas fa-search mr-2"></i>Lihat Lowongan Kerja
                    </a>
                    <a href="{{ route('register') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-50 transition duration-200 text-lg font-medium border border-blue-600">
                        <i class="fas fa-user-plus mr-2"></i>Daftar Akun
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- About Us Section -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $settings['landing_about_title'] ?? 'About farm.girl' }}</h2>
                <p class="text-xl text-gray-600">Our Story</p>
            </div>
            
            <div class="max-w-4xl mx-auto">
                <div class="prose prose-lg mx-auto text-gray-700 whitespace-pre-line">
                    {{ $settings['landing_about_description'] ?? '' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Jobs Preview Section -->
    <div id="jobs" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Lowongan Kerja Terbaru</h2>
                <p class="text-xl text-gray-600">Temukan peluang karir yang sesuai dengan keahlian Anda di food and beverage</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @if($recentJobs->isEmpty())
                    <div class="col-span-full text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-briefcase text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Lowongan</h3>
                        <p class="text-gray-600">Saat ini belum ada lowongan kerja yang tersedia. Silakan cek kembali nanti.</p>
                    </div>
                @else
                    @foreach($recentJobs as $index => $job)
                        @php
                            $animationClass = '';
                            if ($index % 3 == 0) {
                                $animationClass = 'animate__zoomInLeft';
                            } elseif ($index % 3 == 1) {
                                $animationClass = 'animate__zoomInUp';
                            } else {
                                $animationClass = 'animate__zoomInRight';
                            }
                            $delayClass = 'animate-delay-' . (($index % 3) * 100 + 100);
                        @endphp
                        <div class="job-card bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 hover:scale-105 group overflow-hidden relative" data-animation="{{ $animationClass }}" data-delay="{{ $delayClass }}">
                            <!-- Animated background gradient -->
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-indigo-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            
                            <!-- Shine effect -->
                            <div class="shine-effect absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 opacity-20"></div>
                            
                            <div class="relative p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="transform group-hover:translate-x-1 transition-transform duration-300">
                                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">{{ $job->position }}</h3>
                                        <p class="text-sm text-gray-600">{{ $job->company }}</p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full transform group-hover:scale-110 transition-transform duration-300 shadow-sm">
                                        <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse-glow"></span>
                                        Aktif
                                    </span>
                                </div>
                                
                                <div class="space-y-3 mb-4">
                                    <div class="flex items-center text-sm text-gray-600 transform group-hover:translate-x-2 transition-transform duration-300 delay-75">
                                        <i class="fas fa-map-marker-alt mr-3 text-blue-500 group-hover:text-blue-600 transition-colors duration-300"></i>
                                        <span class="group-hover:text-gray-800 transition-colors duration-300">{{ $job->location }}</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600 transform group-hover:translate-x-2 transition-transform duration-300 delay-100">
                                        <i class="fas fa-briefcase mr-3 text-green-500 group-hover:text-green-600 transition-colors duration-300"></i>
                                        <span class="group-hover:text-gray-800 transition-colors duration-300">{{ $job->employment_type }}</span>
                                    </div>
                                    @if(!empty($job->salary_range))
                                        <div class="flex items-center text-sm text-gray-600 transform group-hover:translate-x-2 transition-transform duration-300 delay-125">
                                            <i class="fas fa-money-bill-wave mr-3 text-yellow-500 group-hover:text-yellow-600 transition-colors duration-300"></i>
                                            <span class="group-hover:text-gray-800 transition-colors duration-300">{{ $job->salary_range }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <p class="text-sm text-gray-700 mb-4 group-hover:text-gray-800 transition-colors duration-300">
                                    {{ Str::limit($job->description, 120) }}
                                </p>
                                
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500 group-hover:text-gray-700 transition-colors duration-300">
                                        {{ $job->created_at->diffForHumans() }}
                                    </span>
                                    <a href="{{ route('jobs.show', $job) }}" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 text-sm font-medium transform hover:scale-105 hover:shadow-lg group/btn">
                                        <i class="fas fa-paper-plane mr-1 group-hover/btn:animate-pulse"></i>Lamar Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('jobs.index') }}" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-200 text-lg font-medium">
                    <i class="fas fa-plus mr-2"></i>Lihat Semua Lowongan
                </a>
            </div>
        </div>
    </div>

    <!-- Benefits Section -->
    <div class="bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-white mb-4">OUR BENEFITS</h2>
                <p class="text-xl text-blue-100 mb-8">We offer competitive pay, flexible schedule, BPJS Kesehatan and Ketenagakerjaan, generous meal and drinks discounts, and an exciting potential for growth and a progressive fun work environment.</p>
            </div>
            
            <!-- Benefits Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <!-- Card 1 -->
                <div class="benefit-card bg-white rounded-lg shadow-lg p-6 transform translate-y-8 opacity-0 transition-all duration-700 ease-out">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-money-bill-wave text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Competitive Pay</h3>
                        <p class="text-gray-600">We offer competitive salary packages that recognize your skills and dedication to excellence.</p>
                    </div>
                </div>
                
                <!-- Card 2 -->
                <div class="benefit-card bg-white rounded-lg shadow-lg p-6 transform translate-y-8 opacity-0 transition-all duration-700 ease-out" style="transition-delay: 200ms;">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Flexible Schedule</h3>
                        <p class="text-gray-600">Work-life balance is important to us. Enjoy flexible working hours that fit your lifestyle.</p>
                    </div>
                </div>
                
                <!-- Card 3 -->
                <div class="benefit-card bg-white rounded-lg shadow-lg p-6 transform translate-y-8 opacity-0 transition-all duration-700 ease-out" style="transition-delay: 400ms;">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-heart text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Health & Wellness</h3>
                        <p class="text-gray-600">Complete BPJS Kesehatan and Ketenagakerjaan coverage plus generous meal and drinks discounts.</p>
                    </div>
                </div>
            </div>
            
            <!-- CTA Button -->
            <div class="text-center">
                <a href="{{ route('register') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition duration-200 text-lg font-medium">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang - Gratis!
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}" class="w-8 h-8 object-contain">
                        <h3 class="ml-3 text-xl font-bold">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</h3>
                    </div>
                    <p class="text-gray-400">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }} adalah perusahaan food and beverage terdepan yang berfokus pada makanan cepat saji. Kami mencari talenta terbaik untuk bergabung dengan tim kami yang dinamis dan berdedikasi.</p>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Untuk Pelamar</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('register') }}" class="hover:text-white">Daftar Akun</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white">Masuk</a></li>
                        <li><a href="#jobs" class="hover:text-white">Cari Lowongan</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Perusahaan</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('login') }}" class="hover:text-white">Login HRD</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white">Login Admin</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i>info@rekruter.com</li>
                        <li><i class="fas fa-phone mr-2"></i>+62 21 1234 5678</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>Jakarta, Indonesia</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 {{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Benefits cards animation
        document.addEventListener('DOMContentLoaded', function() {
            const benefitCards = document.querySelectorAll('.benefit-card');
            const jobCards = document.querySelectorAll('.job-card');
            
            // Function to check if element is in viewport
            function isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            }
            
            // Function to animate benefit cards
            function animateBenefitCards() {
                benefitCards.forEach((card, index) => {
                    if (isInViewport(card)) {
                        setTimeout(() => {
                            card.style.transform = 'translateY(0)';
                            card.style.opacity = '1';
                        }, index * 200); // Stagger animation by 200ms
                    }
                });
            }
            
            // Function to animate job cards with Animate.css
            function animateJobCards() {
                jobCards.forEach((card, index) => {
                    if (isInViewport(card)) {
                        if (!card.classList.contains('animate__animated')) {
                            card.classList.add('animate__animated');
                        }
                    }
                });
            }
            
            // Enhanced hover effects for job cards
            jobCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                    this.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.25)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '';
                });
            });
            
            // Initial check
            animateBenefitCards();
            animateJobCards();
            
            // Check on scroll
            window.addEventListener('scroll', function() {
                animateBenefitCards();
                animateJobCards();
            });
            
            // Add intersection observer for scroll-triggered animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        if (!entry.target.classList.contains('animate__animated')) {
                            const card = entry.target;
                            const animationType = card.getAttribute('data-animation');
                            const delay = card.getAttribute('data-delay');
                            
                            card.classList.add('animate__animated', animationType);
                            if (delay) {
                                card.classList.add(delay);
                            }
                        }
                    }
                });
            }, observerOptions);
            
            jobCards.forEach((card) => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
