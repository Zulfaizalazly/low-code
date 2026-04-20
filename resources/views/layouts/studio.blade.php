<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Studio | {{ config('app.name', 'Arrahnumation V3') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-slate-100 selection:bg-indigo-500/30">
    <div class="flex h-full overflow-hidden">
        <!-- Studio Sidebar (Integrated/Sleek) -->
        <aside class="flex-shrink-0 w-72 bg-slate-900/50 backdrop-blur-xl border-r border-slate-800/50 flex flex-col">
            <div class="p-8">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-500 to-purple-500 shadow-lg shadow-indigo-500/20"></div>
                    <span class="text-xl font-bold tracking-tight">HQ Studio</span>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-2">
                <a href="/studio" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 transition-all">
                    Dashboard
                </a>
                
                <div class="pt-6 pb-2 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Engine Builders
                </div>
                
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                    Feature Builder
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                    Flow Canvas
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                    Page Studio
                </a>

                <div class="pt-6 pb-2 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    Governance
                </div>
                
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                    Release Center
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                    Audit Logs
                </a>
            </nav>

            <div class="p-6 border-t border-slate-800/50">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700"></div>
                    <div>
                        <p class="text-xs font-bold">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-[10px] text-slate-500">HQ Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="flex-1 overflow-y-auto bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-slate-950">
            <div class="max-w-7xl mx-auto p-12">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
