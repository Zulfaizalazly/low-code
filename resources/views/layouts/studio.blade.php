<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#fcfcfc]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Studio | {{ config('app.name', 'Arrahnumation V3') }}</title>

    <!-- Fonts: Inter (Apple system-like) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN (works without Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'] },
                }
            }
        }
    </script>

    <!-- Vite (when dev server is running, overrides CDN) -->
    @php try { @endphp
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php } catch(\Exception $e) {} @endphp
    @livewireStyles
    <style>
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        ::-webkit-scrollbar { width: 6px; } 
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 3px; }
        
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.2, 0.8, 0.2, 1), transform 0.3s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.3s;
        }
        
        .main-transition {
            transition: padding-left 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full font-sans antialiased text-[#1d1d1f] selection:bg-blue-500/30">
    <div class="flex h-full overflow-hidden" x-data="{ sidebarOpen: !window.location.pathname.includes('/studio/page-builder') && !window.location.pathname.includes('/studio/flow-canvas') }">
        
        <!-- Studio Sidebar (Apple Aesthetic) -->
        <!-- Studio Sidebar (Apple Aesthetic) -->
        <aside 
            class="flex-shrink-0 bg-[#f5f5f7]/80 backdrop-blur-3xl border-r border-[#1d1d1f]/[0.05] flex flex-col z-40 sidebar-transition"
            :class="sidebarOpen ? 'w-[260px] translate-x-0 opacity-100' : 'w-0 -translate-x-full opacity-0 pointer-events-none'"
        >
            <div class="p-5 pb-2 w-[260px]">
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="w-8 h-8 rounded-[10px] bg-gradient-to-b from-gray-800 to-gray-900 shadow-sm shadow-black/20 flex items-center justify-center border border-gray-700/50">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <span class="text-[16px] font-semibold tracking-[-0.01em] text-[#1d1d1f]">HQ Studio</span>
                </div>
            </div>

            <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto w-[260px] scrollbar-hide">
                <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.dashboard') ? 'bg-[#1d1d1f]/[0.06] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.04] font-medium' }} transition-colors duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.dashboard') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                
                <div class="pt-5 pb-1.5 px-3 text-[11px] font-medium tracking-wide text-[#86868b]/80 uppercase">
                    Engine Builders
                </div>
                
                <div class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 font-medium rounded-[10px] text-[#86868b]/60 cursor-not-allowed">
                    <svg class="w-5 h-5 text-[#86868b]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    <span class="flex-1">Feature Builder</span>
                    <span class="text-[9px] font-semibold px-1.5 py-[1px] rounded text-[#86868b]/60 bg-[#86868b]/10 uppercase tracking-widest">Soon</span>
                </div>
                
                <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.flow-canvas') ? 'bg-[#1d1d1f]/[0.06] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.04] font-medium' }} transition-colors duration-200 group" title="Select a feature from Dashboard to edit its Flow">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.flow-canvas') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    <span>Flow Canvas</span>
                    @if(!request()->routeIs('studio.flow-canvas'))
                        <span class="text-[10px] ml-auto font-medium text-[#86868b]/50 uppercase tracking-wider">Via Dash</span>
                    @endif
                </a>

                <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.page-builder') ? 'bg-[#1d1d1f]/[0.06] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.04] font-medium' }} transition-colors duration-200 group" title="Select a feature from Dashboard to edit its UI Pages">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.page-builder') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                    <span>Page Studio</span>
                    @if(!request()->routeIs('studio.page-builder'))
                        <span class="text-[10px] ml-auto font-medium text-[#86868b]/50 uppercase tracking-wider">Via Dash</span>
                    @endif
                </a>

                <div class="pt-5 pb-1.5 px-3 text-[11px] font-medium tracking-wide text-[#86868b]/80 uppercase">
                    Governance
                </div>
                
                <a href="{{ route('studio.releases') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.releases*') ? 'bg-[#1d1d1f]/[0.06] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.04] font-medium' }} transition-colors duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.releases*') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    Release Center
                </a>
                <a href="{{ route('studio.monitor') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[10px] {{ request()->routeIs('studio.monitor') ? 'bg-[#1d1d1f]/[0.06] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.04] font-medium' }} transition-colors duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('studio.monitor') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    Runtime Monitor
                </a>
                <div class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 font-medium rounded-[10px] text-[#86868b]/60 cursor-not-allowed">
                    <svg class="w-5 h-5 text-[#86868b]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span class="flex-1">Audit Logs</span>
                    <span class="text-[9px] font-semibold px-1.5 py-[1px] rounded text-[#86868b]/60 bg-[#86868b]/10 uppercase tracking-widest">Soon</span>
                </div>
            </nav>

            <div class="p-3 w-[260px]">
                <div class="flex items-center gap-3 p-2 rounded-[12px] hover:bg-[#1d1d1f]/[0.04] transition-colors cursor-pointer group">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#e5e5ea] to-[#f5f5f7] shadow-inner border border-black/5 flex items-center justify-center shrink-0 group-hover:border-black/10 transition-colors">
                        <svg class="w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-[#1d1d1f] truncate leading-tight">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-[11px] font-medium text-[#86868b] truncate leading-tight">HQ Admin</p>
                    </div>
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
