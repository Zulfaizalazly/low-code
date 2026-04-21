<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Arrahnumation V3') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN (renders without Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', '-apple-system', 'sans-serif'] } } } }</script>
    <!-- Vite (when running, overrides CDN) -->
    @php try { @endphp
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php } catch(\Exception $e) {} @endphp
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-slate-900">
    <div class="flex h-full overflow-hidden">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-64 border-r border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
                    <div class="flex items-center flex-shrink-0 px-6 mb-8">
                        <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Arrahnu V3
                        </span>
                    </div>
                    
                    <nav class="flex-1 px-4 space-y-1 bg-white" aria-label="Sidebar">
                        <!-- Dashboard -->
                        <a href="/" class="group flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-slate-100 text-slate-900">
                            Dashboard
                        </a>

                        <div class="pt-4 pb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider px-4">
                            Operational Modules
                        </div>

                        <!-- Dynamic Menus -->
                        <livewire:runtime.sidebar />
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto focus:outline-none">
            <header class="h-16 bg-white border-b border-slate-200 flex items-center px-8 justify-between sticky top-0 z-10">
                <div class="flex items-center gap-4">
                     <!-- Breadcrumbs/Search could go here -->
                </div>
                <div class="flex items-center gap-4 text-sm font-medium text-slate-600">
                    <span>{{ auth()->user()->name ?? 'Guest' }}</span>
                    <div class="w-8 h-8 rounded-full bg-slate-200 border border-slate-300"></div>
                </div>
            </header>

            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
