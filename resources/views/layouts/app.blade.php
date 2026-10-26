<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WOLA MUSASHI') }} - PORTAL WOLA</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Enforce Light Theme Only -->
        <script>
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800">
        
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col lg:flex-row">
            
            <!-- SIDEBAR NAVIGASI DASHBOARD -->
            @include('layouts.sidebar')

            <!-- KONTEN UTAMA (KANAN) -->
            <div class="flex-1 flex flex-col min-w-0 lg:pl-64 transition-all duration-300">
                
                <!-- TOP BAR KHUSUS MOBILE & TABLET (DENGAN HAMBURGER) -->
                <div class="lg:hidden sticky top-0 z-30 h-16 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-3 sm:px-6 shadow-xs">
                    <div class="flex items-center space-x-3">
                        <button @click="sidebarOpen = true" 
                                class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition"
                                title="Buka Menu Sidebar">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div class="flex items-center space-x-2">
                            <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black text-xs shadow-sm">
                                M
                            </div>
                            <span class="font-bold text-xs tracking-tight text-slate-900 dark:text-white">WOLA MUSASHI</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                            {{ Auth::user()->getRoleNames()->first() }}
                        </span>
                    </div>
                </div>

                <!-- PAGE HEADING JIKA TERSEDIA -->
                @isset($header)
                    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-xs shrink-0">
                        <div class="max-w-7xl 2xl:max-w-[1720px] 3xl:max-w-[1920px] w-full mx-auto py-4 sm:py-5 px-3 sm:px-6 lg:px-8 2xl:px-10">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- PAGE CONTENT -->
                <main class="flex-1">
                    {{ $slot }}
                </main>

            </div>

        </div>

    </body>
</html>
