<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f5f5f7]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Branch Operations | {{ config('app.name', 'Arrahnumation V3') }}</title>

    <!-- Fonts: Inter (Apple system-like) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
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
<body class="h-full font-sans antialiased text-[#1d1d1f] selection:bg-blue-500/30">
    <div class="flex h-full overflow-hidden">

        <!-- Branch Sidebar -->
        <aside class="flex-shrink-0 w-[260px] bg-[#f5f5f7]/60 backdrop-blur-3xl border-r border-black/[0.05] flex flex-col z-40">
            <div class="p-5 pb-2">
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="w-9 h-9 rounded-[12px] bg-gradient-to-br from-[#1d1d1f] to-[#434346] shadow-md flex items-center justify-center border border-white/20 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <svg class="w-5 h-5 text-white relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <div>
                        <span class="text-[17px] font-bold tracking-tight text-[#1d1d1f]">Branch Ops</span>
                        <p class="text-[10px] font-bold text-[#86868b] uppercase tracking-widest mt-0.5">Operations Center</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto scrollbar-hide">
                <!-- Dashboard -->
                <a href="{{ route('branch.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('branch.dashboard') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('branch.dashboard') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>

                <div class="pt-6 pb-2 px-3 text-[11px] font-bold tracking-wider text-[#86868b] uppercase">
                    Monitoring
                </div>

                <!-- Staff Activity -->
                <a href="{{ route('branch.staff') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('branch.staff') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('branch.staff') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Staff Activity
                </a>

                <!-- Available Features -->
                <a href="{{ route('branch.features') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('branch.features') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('branch.features') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    Available Features
                </a>

                <div class="pt-6 pb-2 px-3 text-[11px] font-bold tracking-wider text-[#86868b] uppercase">
                    Support
                </div>

                <!-- IT Support -->
                <a href="{{ route('branch.support') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('branch.support') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('branch.support') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    IT Support
                    @php
                        $openTickets = \App\Models\Branch\SupportTicket::forUser(auth()->id())->open()->count();
                    @endphp
                    @if($openTickets > 0)
                        <span class="ml-auto px-2 py-0.5 text-[10px] font-bold bg-amber-100/80 text-amber-700 rounded-full ring-1 ring-amber-500/20">{{ $openTickets }}</span>
                    @endif
                </a>
            </nav>

            <!-- View Mode Toggle -->
            <div class="px-4 pb-2 mt-auto">
                <form action="{{ route('branch.toggle-view') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-between p-3 rounded-[14px] bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100/50 hover:shadow-sm hover:border-blue-200 transition-all group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-[10px] bg-white flex items-center justify-center text-blue-600 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                            </div>
                            <span class="text-[13px] font-bold text-blue-800 tracking-tight">Staff View</span>
                        </div>
                        <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest bg-white px-2 py-0.5 rounded-full shadow-sm">Toggle</span>
                    </button>
                </form>
            </div>

            <!-- User Profile -->
            <div class="px-4 pb-4">
                <div class="flex items-center gap-3 p-3 rounded-[16px] bg-white border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.04)] transition-all group cursor-pointer">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-400 to-teal-500 shadow-inner border border-white/20 flex items-center justify-center shrink-0">
                        <span class="text-[12px] font-bold text-white">{{ substr(auth()->user()->name ?? 'M', 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-[#1d1d1f] truncate leading-tight group-hover:text-emerald-600 transition-colors">{{ auth()->user()->name ?? 'Branch Manager' }}</p>
                        <p class="text-[11px] font-medium text-[#86868b] truncate leading-tight mt-0.5">Manager Profile</p>
                    </div>
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
