<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#fcfcfc]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Studio | {{ config('app.name', 'Arrahnumation V3') }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/arrahnumation.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 3px; }
    </style>
</head>
<body class="h-full font-sans antialiased text-[#1d1d1f] selection:bg-blue-500/30 bg-[#fcfcfc]">
    
    <!-- Fullscreen workspace — no sidebar -->
    <main class="h-full overflow-y-auto">
        <!-- Compact top bar with back navigation -->
        <div class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-black/5">
            <div class="max-w-[1600px] mx-auto px-6 h-12 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-2 text-[#86868b] hover:text-[#1d1d1f] transition-colors group" title="Back to HQ Studio">
                        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        <span class="text-sm font-medium">HQ Studio</span>
                    </a>
                    <svg class="w-3.5 h-3.5 text-[#d1d1d6]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    <span class="text-sm font-semibold text-[#1d1d1f]">@yield('builder-title', 'Builder')</span>
                </div>
                <div class="flex items-center gap-3">
                    @yield('builder-actions')
                    <div class="flex items-center gap-2 pl-3 border-l border-black/5">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-[#e5e5ea] to-[#f5f5f7] shadow-inner border border-black/5 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-xs font-medium text-[#86868b]">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Builder content area — full width -->
        <div class="max-w-[1600px] mx-auto px-6 py-6">
            @if(isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </div>
    </main>

    @stack('scripts')
    @livewireScripts
</body>
</html>
