<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WOLA MUSASHI') }} - PORTAL WOLA</title>

        <!-- Favicon / Logo -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=1">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=1">
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}?v=1">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=1">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=1">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Enforce Clean Light Theme -->
        <script>
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased bg-slate-50 min-h-screen flex flex-col justify-center items-center py-6 sm:py-10 md:py-14 px-4 sm:px-6 lg:px-8 selection:bg-blue-500 selection:text-white">
        <div class="w-full max-w-sm sm:max-w-md lg:max-w-md 2xl:max-w-lg transition-all">
            {{ $slot }}
        </div>
    </body>
</html>
