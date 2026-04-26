<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f5f5f7]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Arrahnumation V3') }}</title>
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
        [x-cloak] { display: none !important; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in-up { animation: fadeInUp 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        .shimmer { background: linear-gradient(90deg, transparent 25%, rgba(255,255,255,0.4) 50%, transparent 75%); background-size: 200% 100%; animation: shimmer 2s infinite; }
    </style>
</head>
<body class="h-full font-sans antialiased text-[#1d1d1f] selection:bg-blue-500/20">
    @if(auth()->check() && auth()->user()->hasRole('branch_manager') && session('branch_view_mode') === 'staff')
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-4 py-2.5 flex items-center justify-between text-white z-[60] relative shadow-md">
            <div class="flex items-center gap-2.5 max-w-7xl mx-auto w-full px-4 sm:px-6 md:px-8">
                <svg class="w-5 h-5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
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
        <!-- Sidebar -->
        <aside class="flex-shrink-0 w-[260px] bg-white/70 backdrop-blur-2xl border-r border-black/[0.06] flex flex-col z-40 h-full">
            <div class="p-5 pb-2">
                <div class="flex items-center gap-3 px-2 py-2">
                    <x-app-logo size="md" class="rounded-[12px] shadow-lg" />
                    <div>
                        <span class="text-[17px] font-bold tracking-tight text-[#1d1d1f]">Arrahnu</span>
                        <p class="text-[10px] font-bold text-[#86868b] uppercase tracking-widest mt-0.5">Branch Portal</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto scrollbar-hide">
                <a href="{{ route('runtime.portal') }}" class="flex items-center gap-3 px-3 py-2.5 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('runtime.portal') ? 'bg-gradient-to-r from-amber-50 to-orange-50 text-amber-900 font-semibold border border-amber-100/60 shadow-sm' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('runtime.portal') ? 'text-amber-600' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>

                <!-- Dynamic Menus -->
                <livewire:runtime.sidebar />

                <!-- Support Link -->
                <div class="pt-4 pb-1 px-3 text-[11px] font-bold tracking-wider text-[#86868b]/70 uppercase">
                    Help
                </div>
                <a href="{{ route('portal.support') }}" class="flex items-center gap-3 px-3 py-2.5 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('portal.support') ? 'bg-gradient-to-r from-amber-50 to-orange-50 text-amber-900 font-semibold border border-amber-100/60 shadow-sm' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('portal.support') ? 'text-amber-600' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    IT Support
                </a>
            </nav>

            <!-- User Profile -->
            <div class="shrink-0 px-4 pb-4">
                <div class="flex items-center gap-3 p-3 rounded-[16px] bg-white border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.04)] transition-all group">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-amber-400 to-orange-500 shadow-inner border border-white/30 flex items-center justify-center shrink-0">
                        <span class="text-[12px] font-bold text-white">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-[#1d1d1f] truncate leading-tight">{{ auth()->user()->name ?? 'Staff' }}</p>
                        <p class="text-[11px] font-medium text-[#86868b] truncate leading-tight mt-0.5">
                            {{ auth()->user()->roles->first()?->name ? ucwords(str_replace(['_', '-'], ' ', auth()->user()->roles->first()->name)) : 'Staff' }}
                        </p>
                    </div>
                    @if(config('app.demo_mode') || app()->environment(['local', 'testing']))
                        <a href="/logout" class="p-1.5 rounded-lg text-[#86868b] hover:text-red-500 hover:bg-red-50 transition-all" title="Switch Role">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        </a>
                    @endif
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto bg-[#f5f5f7] relative min-w-0">
            <div class="p-6 lg:p-8 max-w-full">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
