<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- FontAwesome Icons -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Print Styles -->
        <style>
            @media print {
                /* Sembunyikan sidebar, header, dan tombol aksi saat cetak */
                #sidebar,
                #app-header,
                .print\:hidden {
                    display: none !important;
                }

                /* Reset layout agar konten mengisi seluruh halaman */
                body {
                    background: #fff !important;
                    overflow: visible !important;
                }

                body > div {
                    display: block !important;
                    height: auto !important;
                    overflow: visible !important;
                }

                /* Konten utama mengisi penuh */
                main {
                    overflow: visible !important;
                    flex: none !important;
                }

                /* Pastikan warna background kartu tercetak */
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar Navigation -->
            <div id="sidebar">
                @include('layouts.sidebar')
            </div>

            <!-- Right Content Container -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Header Bar -->
                <div id="app-header">
                    @include('layouts.header')
                </div>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
