<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data
    :class="{ 'dark': $store.theme.isDark }"
    class="h-full"
    x-cloak>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Face Recognition' }}</title>

    {{-- Prevent flicker before Alpine loads --}}
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300">

    {{-- HEADER KIOSK --}}
    @include('layouts.kiosk-header')

    {{-- CONTENT --}}
    <main class="min-h-[calc(100vh-4rem)]">
        @yield('content')
    </main>
    
    <script src="{{ asset('js/face-api.min.js') }}"></script>

    @stack('scripts')
</body>

</html>