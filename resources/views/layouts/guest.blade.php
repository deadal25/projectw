<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WOLA MUSASHI') }} - PORTAL WOLA</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 dark:text-slate-100 antialiased bg-slate-950 min-h-screen flex flex-col justify-center items-center py-6 sm:py-10 md:py-14 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-sm sm:max-w-md lg:max-w-md 2xl:max-w-lg transition-all">
            {{ $slot }}
        </div>
    </body>
</html>
