<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'HRIS' }} | StandardPen</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-white/90">
    <div class="relative min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(70,95,255,0.12),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(70,95,255,0.08),_transparent_28%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(70,95,255,0.18),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(255,255,255,0.04),_transparent_28%)]"></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col px-4 py-4 sm:px-6 lg:px-8">
            <header class="mb-6 flex items-center justify-between rounded-3xl border border-gray-200 bg-white/90 px-5 py-4 shadow-theme-xs backdrop-blur dark:border-gray-800 dark:bg-gray-900/90">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo/standardpen-logo.png') }}" alt="StandardPen logo"
                        class="h-10 w-auto object-contain" />
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white/90">StandardPen</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">HRIS fallback layout</p>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-500/30 dark:hover:text-brand-300">
                            Dashboard
                        </a>
                    @endauth
                </div>
            </header>

            <main class="flex-1 pb-6">
                <div class="mb-6">
                    <x-common.flash-messages />
                </div>
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
