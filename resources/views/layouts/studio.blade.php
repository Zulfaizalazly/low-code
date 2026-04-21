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
        <aside 
            class="flex-shrink-0 bg-white/70 backdrop-blur-2xl border-r border-[#1d1d1f]/10 flex flex-col z-40 sidebar-transition"
            :class="sidebarOpen ? 'w-72 translate-x-0 opacity-100' : 'w-0 -translate-x-full opacity-0 pointer-events-none'"
        >
            <div class="p-8 pb-6 w-72">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-500 to-indigo-500 shadow-md shadow-blue-500/20"></div>
                    <span class="text-xl font-semibold tracking-tight text-[#1d1d1f]">HQ Studio</span>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto w-72">
                <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-[15px] rounded-xl {{ request()->routeIs('studio.dashboard') ? 'bg-[#1d1d1f]/5 text-[#1d1d1f] font-semibold' : 'text-[#86868b] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.02] font-medium' }} transition-all">
                    Dashboard
                </a>
                
                <div class="pt-6 pb-2 px-4 text-[11px] font-semibold tracking-wide text-[#86868b] uppercase">
                    Engine Builders
                </div>
                
                <div class="px-4 py-2.5 text-[15px] font-medium rounded-xl text-[#86868b]/50 cursor-not-allowed">
                    Feature Builder
                    <span class="text-[10px] ml-2 text-[#86868b]/40">(Coming Soon)</span>
                </div>
                
                <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-[15px] rounded-xl {{ request()->routeIs('studio.flow-canvas') ? 'bg-[#1d1d1f]/5 text-[#1d1d1f] font-semibold' : 'text-[#86868b] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.02] font-medium' }} transition-all" title="Select a feature from Dashboard to edit its Flow">
                    Flow Canvas
                    @if(!request()->routeIs('studio.flow-canvas'))
                        <span class="text-[10px] ml-auto text-[#86868b] opacity-60 text-right leading-tight">Select via<br>Dashboard</span>
                    @endif
                </a>

                <a href="{{ route('studio.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-[15px] rounded-xl {{ request()->routeIs('studio.page-builder') ? 'bg-[#1d1d1f]/5 text-[#1d1d1f] font-semibold' : 'text-[#86868b] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.02] font-medium' }} transition-all" title="Select a feature from Dashboard to edit its UI Pages">
                    Page Studio
                    @if(!request()->routeIs('studio.page-builder'))
                        <span class="text-[10px] ml-auto text-[#86868b] opacity-60 text-right leading-tight">Select via<br>Dashboard</span>
                    @endif
                </a>

                <div class="pt-6 pb-2 px-4 text-[11px] font-semibold tracking-wide text-[#86868b] uppercase">
                    Governance
                </div>
                
                <a href="{{ route('studio.releases') }}" class="flex items-center gap-3 px-4 py-2.5 text-[15px] rounded-xl {{ request()->routeIs('studio.releases*') ? 'bg-[#1d1d1f]/5 text-[#1d1d1f] font-semibold' : 'text-[#86868b] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.02] font-medium' }} transition-all">
                    Release Center
                </a>
                <a href="{{ route('studio.monitor') }}" class="flex items-center gap-3 px-4 py-2.5 text-[15px] rounded-xl {{ request()->routeIs('studio.monitor') ? 'bg-[#1d1d1f]/5 text-[#1d1d1f] font-semibold' : 'text-[#86868b] hover:text-[#1d1d1f] hover:bg-[#1d1d1f]/[0.02] font-medium' }} transition-all">
                    Runtime Monitor
                </a>
                <div class="px-4 py-2.5 text-[15px] font-medium rounded-xl text-[#86868b]/50 cursor-not-allowed">
                    Audit Logs
                    <span class="text-[10px] ml-2 text-[#86868b]/40">(Coming Soon)</span>
                </div>
            </nav>

            <div class="p-6 border-t border-[#1d1d1f]/10 bg-[#f5f5f7]/50 backdrop-blur-md w-72">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-gray-200 to-gray-300 shadow-inner flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <div>
                        <p class="text-[14px] font-semibold text-[#1d1d1f] tracking-tight">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-[11px] text-[#86868b]">HQ Admin</p>
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
