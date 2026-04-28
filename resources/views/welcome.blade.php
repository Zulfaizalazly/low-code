<!DOCTYPE html>
<html lang="en" class="antialiased font-sans">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kopsya Ar-Rahnu - Welcome</title>
    <link rel="icon" type="image/png" href="{{ asset('images/KOPSYA-final-logo-tagline-OL2-Copy-175x96.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #fcfcfc;
            overflow-x: hidden;
            margin: 0;
            color: #1d1d1f;
        }

        /* Abstract Parallax Background */
        .ambient-layer {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 20s infinite ease-in-out alternate;
            will-change: transform;
        }

        .orb-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(162,194,228,1) 0%, rgba(206,231,250,0) 70%);
            top: -10%; left: -10%;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(239,213,248,1) 0%, rgba(248,236,252,0) 70%);
            bottom: -5%; right: -5%;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(200,240,220,1) 0%, rgba(220,250,230,0) 70%);
            top: 40%; left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -10s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 30px) scale(1.05); }
            100% { transform: translate(-30px, 60px) scale(0.95); }
        }

        /* Glassmorphism & Cards */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .role-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 2.5rem 1.5rem;
            border-radius: 20px;
        }

        .role-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.06);
            background: rgba(255, 255, 255, 1);
        }

        .role-icon {
            width: 64px;
            height: 64px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: linear-gradient(135deg, #f5f5f7 0%, #e8e8ed 100%);
            color: #1d1d1f;
            transition: all 0.3s ease;
        }

        .role-card:hover .role-icon {
            background: #1d1d1f;
            color: #ffffff;
        }

        .role-title {
            font-weight: 600;
            font-size: 1.125rem;
            letter-spacing: -0.01em;
            margin-bottom: 0.5rem;
        }

        .role-desc {
            font-size: 0.875rem;
            color: #86868b;
            line-height: 1.4;
        }

        .system-title {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: -0.04em;
            background: linear-gradient(135deg, #1d1d1f 0%, #434346 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .system-subtitle {
            font-size: 1.25rem;
            color: #86868b;
            letter-spacing: -0.01em;
            font-weight: 400;
        }

        /* Subtle load fade in */
        .fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center relative selection:bg-black selection:text-white">

    <!-- Ambient Parallax Background -->
    <div class="ambient-layer" id="parallax-bg">
        <div class="orb orb-1" data-speed="0.04"></div>
        <div class="orb orb-2" data-speed="-0.03"></div>
        <div class="orb orb-3" data-speed="0.02"></div>
    </div>

    <!-- Main Content -->
    <div class="w-full max-w-5xl px-6 py-12 relative z-10 flex flex-col items-center">
        
        <!-- Authenticated User Indicator -->
        @auth
        <div class="w-full flex justify-end mb-4 fade-in-up" style="max-width: 64rem;">
            <div class="flex items-center gap-3 px-4 py-2 rounded-full" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <span class="text-sm text-gray-600 font-medium">
                    {{ Auth::user()->name ?? Auth::user()->email }}
                </span>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-medium px-3 py-1 rounded-full transition-colors" style="background: rgba(0,0,0,0.05); color: #86868b;" onmouseover="this.style.background='rgba(0,0,0,0.1)';this.style.color='#1d1d1f'" onmouseout="this.style.background='rgba(0,0,0,0.05)';this.style.color='#86868b'">
                        Logout
                    </button>
                </form>
            </div>
        </div>
        @endauth

        <!-- Access Denied Notice -->
        @if(session('access_denied'))
        <div class="w-full max-w-2xl mb-8 fade-in-up" style="max-width: 42rem;">
            <div class="flex items-start gap-3 px-5 py-4 rounded-2xl" style="background: rgba(254,243,199,0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(217,169,56,0.2); box-shadow: 0 2px 12px rgba(0,0,0,0.04);">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 mt-0.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <p class="text-sm text-green-800 leading-relaxed">{{ session('access_denied') }}</p>
            </div>
        </div>
        @endif

        <!-- Header -->
        <header class="text-center mb-16 fade-in-up">
            <img src="{{ asset('images/KOPSYA-final-logo-tagline-OL2-Copy-175x96.png') }}" alt="Kopsya Ar-Rahnu" class="h-24 w-auto mx-auto mb-6">
            <h1 class="system-title">Kopsya Ar-Rahnu</h1>
            <p class="system-subtitle max-w-lg mx-auto">
                Sistem Operasi Ar-Rahnu. Pilih ruang kerja anda untuk meneruskan.
            </p>
            @if(config('app.demo_mode'))
            <span class="inline-block mt-4 px-4 py-1.5 text-xs font-semibold tracking-widest uppercase bg-green-100 text-green-700 rounded-full border border-green-200">
                Demo Mode
            </span>
            @endif
        </header>

        <!-- Workspace Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 w-full max-w-5xl fade-in-up delay-1">
            
            <!-- Branch Teller -->
            @guest
            <div onclick="Livewire.dispatch('openLoginModal', { targetUrl: '/portal/operations/new-pledge' })" class="role-card group">
            @endguest
            @auth
            <a href="/portal/operations/new-pledge" class="role-card group">
            @endauth
                <div class="role-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <h3 class="role-title">Branch Teller</h3>
                <p class="role-desc">Pledge intake, redemptions, and daily customer servicing.</p>
            @guest
            </div>
            @endguest
            @auth
            </a>
            @endauth

            <!-- Branch Manager -->
            @guest
            <div onclick="Livewire.dispatch('openLoginModal', { targetUrl: '/branch' })" class="role-card group">
            @endguest
            @auth
            <a href="/branch" class="role-card group">
            @endauth
                <div class="role-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="role-title">Branch Manager</h3>
                <p class="role-desc">Approve high-value transactions and monitor branch health.</p>
            @guest
            </div>
            @endguest
            @auth
            </a>
            @endauth

            <!-- HQ Studio -->
            @guest
            <div onclick="Livewire.dispatch('openLoginModal', { targetUrl: '/studio' })" class="role-card group">
            @endguest
            @auth
            <a href="/studio" class="role-card group">
            @endauth
                <div class="role-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <h3 class="role-title">HQ Studio</h3>
                <p class="role-desc">Workflows, features, and system orchestration.</p>
            @guest
            </div>
            @endguest
            @auth
            </a>
            @endauth

            <!-- Admin Panel -->
            @guest
            <div onclick="Livewire.dispatch('openLoginModal', { targetUrl: '/admin' })" class="role-card group">
            @endguest
            @auth
            <a href="/admin" class="role-card group">
            @endauth
                <div class="role-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </div>
                <h3 class="role-title">Admin Panel</h3>
                <p class="role-desc">Organization, branches, staff, and entity settings.</p>
            @guest
            </div>
            @endguest
            @auth
            </a>
            @endauth

        </div>

        <!-- Footer Notice -->
        <div class="mt-16 text-center fade-in-up delay-2">
            <p class="text-sm text-gray-400">
                Kopsya Ar-Rahnu &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    <!-- Login Modal (Livewire) -->
    @guest
    <livewire:login-modal />
    @endguest

    <!-- Mouse Parallax Script -->
    <script>
        document.addEventListener('mousemove', function(e) {
            const orbs = document.querySelectorAll('.orb');
            const x = (e.clientX / window.innerWidth) - 0.5;
            const y = (e.clientY / window.innerHeight) - 0.5;

            orbs.forEach(orb => {
                const speed = parseFloat(orb.getAttribute('data-speed'));
                const xOffset = x * speed * 400; // Multiplier for visual effect
                const yOffset = y * speed * 400;
                
                // Keep existing translate-50 property for center orb via Regex or base
                if(orb.classList.contains('orb-3')){
                    orb.style.transform = `translate(calc(-50% + ${xOffset}px), calc(-50% + ${yOffset}px))`;
                } else {
                    orb.style.transform = `translate(${xOffset}px, ${yOffset}px)`;
                }
            });
        });
    </script>
    @livewireScripts
</body>
</html>
