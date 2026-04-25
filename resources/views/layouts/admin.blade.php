<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f5f5f7]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin | {{ config('app.name', 'Arrahnumation V3') }}</title>

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

        <!-- Admin Sidebar -->
        <aside class="flex-shrink-0 w-[260px] bg-[#f5f5f7]/60 backdrop-blur-3xl border-r border-black/[0.05] flex flex-col z-40 h-full overflow-hidden">
            <div class="p-5 pb-2">
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="w-9 h-9 rounded-[12px] bg-gradient-to-br from-[#1d1d1f] to-[#434346] shadow-md flex items-center justify-center border border-white/20 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <svg class="w-5 h-5 text-white relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <div>
                        <span class="text-[17px] font-bold tracking-tight text-[#1d1d1f]">Admin</span>
                        <p class="text-[10px] font-bold text-[#86868b] uppercase tracking-widest mt-0.5">Organization Management</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto scrollbar-hide min-h-0">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('admin.dashboard') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>

                <!-- Organization Section -->
                <div class="pt-6 pb-2 px-3 text-[11px] font-bold tracking-wider text-[#86868b] uppercase">
                    Organization
                </div>

                <!-- Branches -->
                <a href="{{ route('admin.branches') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('admin.branches*') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.branches*') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    Branches
                </a>

                <!-- Departments -->
                <a href="{{ route('admin.departments') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('admin.departments') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.departments') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    Departments
                </a>

                <!-- People Section -->
                <div class="pt-6 pb-2 px-3 text-[11px] font-bold tracking-wider text-[#86868b] uppercase">
                    People
                </div>

                <!-- Staff -->
                <a href="{{ route('admin.staff') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('admin.staff') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.staff') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Staff
                </a>

                <!-- User Roles -->
                <a href="{{ route('admin.staff') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('admin.users.roles') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.users.roles') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    <div class="flex flex-col">
                        <span>User Roles</span>
                        <span class="text-[10px] font-medium text-[#86868b] -mt-0.5">via Staff page</span>
                    </div>
                </a>

                <!-- Settings Section -->
                <div class="pt-6 pb-2 px-3 text-[11px] font-bold tracking-wider text-[#86868b] uppercase">
                    Settings
                </div>

                <!-- Entity Settings -->
                <a href="{{ route('admin.entity') }}" class="flex items-center gap-3 px-3 py-2 text-[14px] leading-5 rounded-[12px] {{ request()->routeIs('admin.entity') ? 'bg-black/[0.04] text-[#1d1d1f] font-semibold' : 'text-[#515154] hover:text-[#1d1d1f] hover:bg-black/[0.03] font-medium' }} transition-all duration-200 group">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.entity') ? 'text-[#1d1d1f]' : 'text-[#86868b] group-hover:text-[#515154]' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Entity Settings
                </a>
            </nav>

            <!-- Quick Access: Studio Dashboard -->
            <div class="shrink-0 px-4 pb-2 mt-auto">
                <a href="{{ route('studio.dashboard') }}" class="w-full flex items-center justify-between p-3 rounded-[14px] bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100/50 hover:shadow-sm hover:border-blue-200 transition-all group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-[10px] bg-white flex items-center justify-center text-blue-600 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                        <span class="text-[13px] font-bold text-blue-800 tracking-tight">Studio</span>
                    </div>
                    <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest bg-white px-2 py-0.5 rounded-full shadow-sm">Go</span>
                </a>
            </div>

            <!-- User Profile -->
            <div class="shrink-0 px-4 pb-4">
                <div class="flex items-center gap-3 p-3 rounded-[16px] bg-white border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.04)] transition-all group cursor-pointer">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-violet-400 to-purple-500 shadow-inner border border-white/20 flex items-center justify-center shrink-0">
                        <span class="text-[12px] font-bold text-white">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[14px] font-bold text-[#1d1d1f] truncate leading-tight group-hover:text-violet-600 transition-colors">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-[11px] font-medium text-[#86868b] truncate leading-tight mt-0.5">
                            @if(auth()->user() && auth()->user()->roles->isNotEmpty())
                                {{ auth()->user()->roles->first()->name }}
                            @else
                                Super Admin
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="flex-1 overflow-y-auto bg-[#f5f5f7] relative">
            <div class="max-w-7xl mx-auto p-10 min-h-full flex flex-col">
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
