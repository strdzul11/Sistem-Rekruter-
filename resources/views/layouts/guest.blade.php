<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- FontAwesome Icons -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4 py-12">
            <!-- Logo dan Header -->
            <div class="text-center mb-8">
                <a href="/" class="inline-flex items-center justify-center w-16 h-16 mb-4">
                    <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}" class="w-16 h-16 object-contain">
                </a>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</h1>
                <p class="text-gray-600 font-medium">Platform Rekrutmen Karyawan Baru</p>
            </div>

            <!-- Card Content -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-white/40">
                {{ $slot }}
            </div>

            <a href="{{ route('landing') }}" class="mt-4 w-full flex items-center justify-center bg-white text-blue-600 py-3 px-4 rounded-xl hover:bg-blue-50 transition duration-200 font-semibold border border-blue-200 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
            </a>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-500 text-xs font-semibold">
                <p>&copy; {{ date('Y') }} {{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}. All rights reserved.</p>
            </div>
        </div>
    </body>
</html>
