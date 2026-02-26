<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EasyColoc') }} — {{ $title ?? 'Dashboard' }}</title>
    <meta name="description" content="EasyColoc — Shared housing and expense management made simple.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-[#f2f5f9]">

    <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white border-b border-[#dae2ec]">
                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Flash Notifications -->
        @if(session('status'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2" class="fixed top-4 right-4 z-50 flex items-center gap-3 bg-white border border-[#dae2ec]
                           rounded-2xl shadow-lg px-5 py-3 text-sm font-semibold text-[#2563eb]">
                <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
                <button @click="show = false"
                    class="ml-1 text-[#657e9a] hover:text-[#142c3e] transition-colors text-base leading-none">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
                x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed top-4 right-4 z-50 flex items-center gap-3 bg-white border border-red-200
                           rounded-2xl shadow-lg px-5 py-3 text-sm font-semibold text-red-600">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v4m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z" />
                </svg>
                <span>{{ session('error') }}</span>
                <button @click="show = false"
                    class="ml-1 text-red-400 hover:text-red-700 transition-colors text-base leading-none">✕</button>
            </div>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>