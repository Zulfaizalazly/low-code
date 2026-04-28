<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f5f5f7]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kopsya Ar-Rahnu') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/KOPSYA-final-logo-tagline-OL2-Copy-175x96.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
        [x-cloak] { display: none !important; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in-up { animation: fadeInUp 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        .shimmer { background: linear-gradient(90deg, transparent 25%, rgba(255,255,255,0.4) 50%, transparent 75%); background-size: 200% 100%; animation: shimmer 2s infinite; }
    </style>
</head>
<body class="h-full font-sans antialiased text-[#1d1d1f] selection:bg-green-500/20">
    @if(auth()->check() && auth()->user()->hasRole('branch_manager') && session('branch_view_mode') === 'staff')
        <div class="bg-[#1b6b2f] px-4 py-2.5 flex items-center justify-between text-white z-[60] relative shadow-md">
            <div class="flex items-center gap-2.5 max-w-7xl mx-auto w-full px-4 sm:px-6 md:px-8">
                <svg class="w-5 h-5 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                <span class="text-[13px] font-bold tracking-wide">Manager Staff View Active <span class="opacity-80 font-normal ml-1">— Full staff capabilities for this branch.</span></span>
                <form action="{{ route('branch.toggle-view') }}" method="POST" class="ml-auto">
                    @csrf
                    <button type="submit" class="text-[12px] font-bold px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-lg border border-white/20 transition-colors flex items-center gap-2">
                        Return to Ops
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="flex h-full overflow-hidden @if(auth()->check() && auth()->user()->hasRole('branch_manager') && session('branch_view_mode') === 'staff') h-[calc(100%-48px)] @endif">
        <!-- Sidebar (Kopsya AR-Rahnu Theme) -->
        <aside class="flex-shrink-0 w-[200px] bg-[#0a1628] flex flex-col z-40 h-full">
            <!-- Logo & Branding -->
            <div class="px-4 pt-5 pb-4">
                <div class="flex items-center gap-3">
                    <x-app-logo size="sm" class="rounded-[8px]" />
                    <div>
                        <span class="text-[14px] font-bold tracking-tight text-white uppercase">AR-RAHNU</span>
                        <p class="text-[9px] font-medium text-white/40 tracking-wide">Islamic Pawnbroking</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto scrollbar-hide">
                <a href="{{ route('runtime.portal') }}" class="flex items-center gap-3 px-3 py-2 text-[13px] leading-5 rounded-lg {{ request()->routeIs('runtime.portal') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-all duration-200 group">
                    <span class="w-2 h-2 rounded-full {{ request()->routeIs('runtime.portal') ? 'bg-[#2ecc71]' : 'bg-white/20 group-hover:bg-white/40' }} transition-colors shrink-0"></span>
                    Dashboard
                </a>

                <!-- Dynamic Menus -->
                <livewire:runtime.sidebar />

                <!-- Support Link -->
                <div class="pt-4 pb-1 px-3 text-[10px] font-bold tracking-wider text-white/25 uppercase">
                    Help
                </div>
                <a href="{{ route('portal.support') }}" class="flex items-center gap-3 px-3 py-2 text-[13px] leading-5 rounded-lg {{ request()->routeIs('portal.support') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-all duration-200 group">
                    <span class="w-2 h-2 rounded-full {{ request()->routeIs('portal.support') ? 'bg-[#2ecc71]' : 'bg-white/20 group-hover:bg-white/40' }} transition-colors shrink-0"></span>
                    IT Support
                </a>
            </nav>

            <!-- Bottom Branding -->
            <div class="shrink-0 px-4 pb-4 pt-2 border-t border-white/[0.06]">
                <div class="flex items-center gap-2 mb-2">
                    <x-app-logo size="xs" class="rounded-[6px] opacity-80" />
                    <div>
                        <p class="text-[11px] font-bold text-white/70">AR-Rahnu</p>
                        <p class="text-[9px] text-white/30 font-medium">Trusted. Shariah Compliant.</p>
                    </div>
                </div>
                <div class="text-[10px] text-white/25 font-medium space-y-0.5">
                    <p id="portal-date">{{ now()->format('d M Y') }}</p>
                    <p id="portal-time">{{ now()->format('h:i:s A') }}</p>
                    <p>v{{ config('app.version', '1.0.0') }}</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 h-full">
            <!-- Green Header Bar -->
            <header class="shrink-0 bg-[#1b6b2f] h-[52px] flex items-center justify-between px-6 shadow-sm z-30">
                <!-- Page Title -->
                <h1 class="text-[18px] font-bold text-white tracking-tight">
                    @hasSection('page-title')
                        @yield('page-title')
                    @else
                        {{ $pageTitle ?? 'Dashboard' }}
                    @endif
                </h1>

                <!-- Right Side: Branch Selector + User -->
                <div class="flex items-center gap-4">
                    <!-- Branch Indicator -->
                    @if(auth()->user()->branch)
                    <div class="flex items-center gap-2 text-white/90">
                        <svg class="w-4 h-4 text-white/60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="text-[13px] font-semibold">{{ auth()->user()->branch->name ?? 'No Branch' }}</span>
                    </div>
                    @endif

                    <!-- User Profile -->
                    <div class="flex items-center gap-2.5 pl-4 border-l border-white/20">
                        <div class="w-8 h-8 rounded-full bg-white/20 border border-white/10 flex items-center justify-center shrink-0">
                            <span class="text-[11px] font-bold text-white">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-[12px] font-semibold text-white leading-tight">{{ auth()->user()->name ?? 'Staff' }}</p>
                            <p class="text-[10px] text-white/60 leading-tight">
                                {{ auth()->user()->roles->first()?->name ? ucwords(str_replace(['_', '-'], ' ', auth()->user()->roles->first()->name)) : 'Staff' }}
                            </p>
                        </div>
                        @if(config('app.demo_mode') || app()->environment(['local', 'testing']))
                            <a href="/logout" class="p-1.5 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-all ml-1" title="Switch Role">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-[#f5f5f7] relative min-w-0">
                <div class="p-6 lg:p-8 max-w-full">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Live Clock Script -->
    <script>
        setInterval(function() {
            const el = document.getElementById('portal-time');
            if (el) {
                const now = new Date();
                let h = now.getHours(), m = now.getMinutes(), s = now.getSeconds();
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12; h = h ? h : 12;
                el.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0') + ' ' + ampm;
            }
        }, 1000);
    </script>

    @livewireScripts
</body>
</html>
