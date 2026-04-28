<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#fcfcfc]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Studio | {{ config('app.name', 'Kopsya Ar-Rahnu') }}</title>
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
        
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.2, 0.8, 0.2, 1), transform 0.3s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.3s;
        }
        
        .main-transition {
            transition: padding-left 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full font-sans antialiased text-[#1d1d1f] selection:bg-green-500/30">
    <div class="flex h-full overflow-hidden" x-data="{ sidebarOpen: !window.location.pathname.includes('/studio/page-builder') && !window.location.pathname.includes('/studio/flow-canvas') }">
        
        <!-- Studio Sidebar (Kopsya AR-Rahnu Theme) -->
        <aside 
            class="flex-shrink-0 bg-[#0a1628] flex flex-col z-40 sidebar-transition"
            :class="sidebarOpen ? 'w-[260px] translate-x-0 opacity-100' : 'w-0 -translate-x-full opacity-0 pointer-events-none'"
        >
            <div class="p-5 pb-2 w-[260px]">
                <div class="flex items-center gap-3 px-2 py-2">
                    <x-app-logo size="sm" class="rounded-[10px]" />
                    <span class="text-[16px] font-semibold tracking-[-0.01em] text-white">HQ Studio</span>
                </div>
            </div>

            <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto w-[260px] scrollbar-hide">
                <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.dashboard') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-colors duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.dashboard') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                
                <div class="pt-5 pb-1.5 px-3 text-[11px] font-medium tracking-wide text-white/30 uppercase">
                    Engine Builders
                </div>
                
                <div class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 font-medium rounded-[10px] text-white/25 cursor-not-allowed">
                    <svg class="w-5 h-5 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    <span class="flex-1">Feature Builder</span>
                    <span class="text-[9px] font-semibold px-1.5 py-[1px] rounded text-white/30 bg-white/10 uppercase tracking-widest">Soon</span>
                </div>
                
                <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.flow-canvas') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-colors duration-200 group" title="Select a feature from Dashboard to edit its Flow">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.flow-canvas') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    <span>Flow Canvas</span>
                    @if(!request()->routeIs('studio.flow-canvas'))
                        <span class="text-[10px] ml-auto font-medium text-white/25 uppercase tracking-wider">Via Dash</span>
                    @endif
                </a>

                <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.page-builder') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-colors duration-200 group" title="Select a feature from Dashboard to edit its UI Pages">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.page-builder') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                    <span>Page Studio</span>
                    @if(!request()->routeIs('studio.page-builder'))
                        <span class="text-[10px] ml-auto font-medium text-white/25 uppercase tracking-wider">Via Dash</span>
                    @endif
                </a>

                <div class="pt-5 pb-1.5 px-3 text-[11px] font-medium tracking-wide text-white/30 uppercase">
                    Governance
                </div>
                
                <a href="{{ route('studio.releases') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.releases*') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-colors duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.releases*') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    Release Center
                </a>
                <a href="{{ route('studio.monitor') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.monitor') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-colors duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.monitor') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    Runtime Monitor
                </a>
                <a href="{{ route('studio.audit') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.audit') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-colors duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.audit') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Audit Logs
                </a>
                <a href="{{ route('studio.support') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.support') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-colors duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.support') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Support
                </a>
            </nav>

            @if(auth()->user()?->hasRole('super-admin'))
            <div class="px-3 pb-1 w-[260px]">
                <a href="{{ route('admin.dashboard') }}" class="w-full flex items-center justify-between p-2.5 rounded-[12px] bg-white/[0.06] border border-white/[0.08] hover:bg-white/[0.1] hover:border-white/[0.15] transition-all group">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-[8px] bg-white/10 flex items-center justify-center text-white/70 group-hover:bg-[#1b6b2f] group-hover:text-white transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <span class="text-[12px] font-bold text-white/70 tracking-tight">Admin Panel</span>
                    </div>
                    <svg class="w-4 h-4 text-white/30 group-hover:text-white/60 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
            @endif

            <div class="p-3 pt-1 w-[260px]">
                <div class="flex items-center gap-3 p-2 rounded-[12px] hover:bg-white/[0.06] transition-colors group">
                    <div class="w-8 h-8 rounded-full bg-[#1b6b2f] shadow-inner border border-white/10 flex items-center justify-center shrink-0">
                        <span class="text-[11px] font-bold text-white">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-white truncate leading-tight">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-[11px] font-medium text-white/40 truncate leading-tight">
                            @if(auth()->user()?->roles?->isNotEmpty())
                                {{ ucwords(str_replace('-', ' ', auth()->user()->roles->first()->name)) }}
                            @else
                                HQ Admin
                            @endif
                        </p>
                    </div>
                    @if(config('app.demo_mode') || app()->environment(['local', 'testing']))
                        <a href="/logout" class="p-1.5 rounded-lg text-white/40 hover:text-red-400 hover:bg-red-500/10 transition-all" title="Switch Role">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        </a>
                    @endif
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="flex-1 overflow-y-auto bg-[#fcfcfc] relative">
            <!-- Sidebar Toggle Button -->
            <button 
                @click="sidebarOpen = !sidebarOpen"
                class="absolute top-6 z-30 p-2 bg-white/80 backdrop-blur-md rounded-xl shadow-sm border border-gray-200/60 text-[#86868b] hover:text-[#1d1d1f] hover:bg-white hover:shadow-md transition-all main-transition"
                :class="sidebarOpen ? 'left-6' : 'left-8'"
                title="Toggle Sidebar Menu"
            >
                <!-- Close Icon -->
                <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                <!-- Open Icon (Hamburger/Menu) -->
                <svg x-cloak x-show="!sidebarOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>

            <!-- Dynamic padding to push content right when sidebar overlaps or button occupies space -->
            <div class="max-w-7xl mx-auto main-transition" :class="sidebarOpen ? 'p-12 pl-12' : 'py-12 pr-12 pl-24'">
                @if(isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </div>
        </main>
    </div>

    @stack('scripts')
    @livewireScripts
</body>
</html>
