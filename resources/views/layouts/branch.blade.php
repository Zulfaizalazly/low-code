<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f5f5f7]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Branch Operations | {{ config('app.name', 'Kopsya Ar-Rahnu') }}</title>
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up {
            animation: fadeInUp 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-[#1d1d1f] selection:bg-green-500/30">
    <div class="flex h-full overflow-hidden">

        <!-- Branch Sidebar (Kopsya AR-Rahnu Theme) -->
        <aside class="flex-shrink-0 w-[260px] bg-[#0a1628] flex flex-col z-40">
            <div class="p-5 pb-2">
                <div class="flex items-center gap-3 px-2 py-2">
                    <x-app-logo size="md" class="rounded-[12px] shadow-md" />
                    <div>
                        <span class="text-[17px] font-bold tracking-tight text-white">Branch Ops</span>
                        <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-0.5">Operations Center</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto scrollbar-hide">
                <!-- Dashboard -->
                <a href="{{ route('branch.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('branch.dashboard') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('branch.dashboard') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>

                <div class="pt-6 pb-2 px-3 text-[11px] font-bold tracking-wider text-white/30 uppercase">
                    Monitoring
                </div>

                <!-- Staff Activity -->
                <a href="{{ route('branch.staff') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('branch.staff') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('branch.staff') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Staff Activity
                </a>

                <!-- Available Features -->
                <a href="{{ route('branch.features') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('branch.features') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('branch.features') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    Available Features
                </a>

                <div class="pt-6 pb-2 px-3 text-[11px] font-bold tracking-wider text-white/30 uppercase">
                    Support
                </div>

                <!-- IT Support -->
                <a href="{{ route('branch.support') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('branch.support') ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('branch.support') ? 'text-[#2ecc71]' : 'text-white/40 group-hover:text-white/60' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    IT Support
                    @php
                        $sidebarOpenTickets = \App\Models\Branch\SupportTicket::forUser(auth()->id())->open()->count();
                    @endphp
                    @if($sidebarOpenTickets > 0)
                        <span class="ml-auto px-2 py-0.5 text-[10px] font-bold bg-[#2ecc71]/20 text-[#2ecc71] rounded-full ring-1 ring-[#2ecc71]/30">{{ $sidebarOpenTickets }}</span>
                    @endif
                </a>
            </nav>

            <!-- View Mode Toggle -->
            <div class="px-4 pb-2 mt-auto">
                <form action="{{ route('branch.toggle-view') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-between p-3 rounded-[14px] bg-[#1b6b2f]/20 border border-[#1b6b2f]/30 hover:bg-[#1b6b2f]/30 hover:border-[#1b6b2f]/50 transition-all group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-[10px] bg-[#1b6b2f] flex items-center justify-center text-white shadow-sm group-hover:bg-[#239b40] transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                            </div>
                            <span class="text-[13px] font-bold text-white/80 tracking-tight">Staff View</span>
                        </div>
                        <span class="text-[10px] font-bold text-[#2ecc71] uppercase tracking-widest bg-white/10 px-2 py-0.5 rounded-full">Toggle</span>
                    </button>
                </form>
            </div>

            <!-- User Profile -->
            <div class="px-4 pb-4">
                <div class="flex items-center gap-3 p-3 rounded-[16px] bg-white/[0.05] border border-white/[0.08] hover:bg-white/[0.08] transition-all group cursor-pointer">
                    <div class="w-9 h-9 rounded-full bg-[#1b6b2f] shadow-inner border border-white/10 flex items-center justify-center shrink-0">
                        <span class="text-[12px] font-bold text-white">{{ substr(auth()->user()->name ?? 'M', 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-white truncate leading-tight group-hover:text-[#2ecc71] transition-colors">{{ auth()->user()->name ?? 'Branch Manager' }}</p>
                        <p class="text-[11px] font-medium text-white/40 truncate leading-tight mt-0.5">Manager Profile</p>
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
        <main class="flex-1 overflow-y-auto bg-[#f5f5f7] relative">
            <div class="max-w-7xl mx-auto p-10">
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
