<div wire:poll.{{ config('branch.dashboard.poll_interval', '30s') }} class="max-w-6xl mx-auto">
    <header class="mb-10 fade-in-up">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-[32px] font-bold tracking-[-0.02em] text-[#1d1d1f] leading-none">Branch Operations</h1>
                <p class="text-[15px] font-medium text-[#86868b]">Real-time overview of your branch activity and feature availability.</p>
            </div>
            <div class="flex items-center gap-2.5 bg-white/60 backdrop-blur-xl border border-black/[0.04] px-3.5 py-1.5 rounded-full shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-[12px] font-semibold text-[#1d1d1f] uppercase tracking-wide">Live Updates</span>
            </div>
        </div>
    </header>

    {{-- ─── Stats Cards ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-10">
        {{-- Active Features --}}
        <div class="group p-6 rounded-[24px] bg-white border border-black/[0.03] shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-500 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-white/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4 relative">
                <p class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Active Features</p>
                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100/50 flex items-center justify-center text-blue-500 shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
            </div>
            <p class="text-[32px] font-bold text-[#1d1d1f] tracking-tight relative leading-none mb-2">{{ $activeFeatures }}</p>
            <p class="text-[13px] font-medium text-[#86868b] relative">Published & available</p>
        </div>

        {{-- Staff Active Now --}}
        <div class="group p-6 rounded-[24px] bg-white border border-black/[0.03] shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-500 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-white/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4 relative">
                <p class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Staff Active</p>
                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100/50 flex items-center justify-center text-indigo-500 shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
            </div>
            <p class="text-[32px] font-bold text-[#1d1d1f] tracking-tight relative leading-none mb-2">{{ $staffActiveNow }}</p>
            <p class="text-[13px] font-medium text-[#86868b] relative">In the last 15 minutes</p>
        </div>

        {{-- Usage Today --}}
        <div class="group p-6 rounded-[24px] bg-white border border-black/[0.03] shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-500 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-white/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4 relative">
                <p class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Usage Today</p>
                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100/50 flex items-center justify-center text-emerald-500 shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </div>
            </div>
            <p class="text-[32px] font-bold text-[#1d1d1f] tracking-tight relative leading-none mb-2">{{ $usageToday }}</p>
            <p class="text-[13px] font-medium text-[#86868b] relative truncate">~{{ $avgPerHour }}/hr &middot; {{ $usageThisWeek }} this wk</p>
        </div>

        {{-- Health Status --}}
        <div class="group p-6 rounded-[24px] bg-white border border-{{ $healthStatus['color'] }}-500/10 shadow-[0_8px_30px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-500 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-{{ $healthStatus['color'] }}-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-4 relative">
                <p class="text-[12px] font-semibold text-{{ $healthStatus['color'] }}-600 uppercase tracking-wider">System Health</p>
                <div class="w-10 h-10 rounded-[14px] bg-{{ $healthStatus['color'] }}-50 border border-{{ $healthStatus['color'] }}-100/50 flex items-center justify-center text-{{ $healthStatus['color'] }}-500 shadow-sm">
                    @if($healthStatus['color'] === 'emerald')
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                    @endif
                </div>
            </div>
            <p class="text-[20px] font-bold text-{{ $healthStatus['color'] }}-600 tracking-tight leading-snug relative mt-2">{{ $healthStatus['label'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        {{-- ─── Change Tracker / Recent Deployments ─── --}}
        <div class="lg:col-span-2 bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.03)] rounded-[24px] overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-black/[0.03] bg-white flex justify-between items-center z-10 relative">
                <div>
                    <h3 class="text-[15px] font-bold text-[#1d1d1f] tracking-tight">Recent IT Deployments</h3>
                    <p class="text-[13px] font-medium text-[#86868b] mt-0.5">Track latest system updates and fixes</p>
                </div>
                <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100">
                    <svg class="w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
            </div>

            <div class="divide-y divide-black/[0.03] flex-1 bg-white">
                @forelse($recentDeployments as $deployment)
                    <div class="px-6 py-4 flex items-center gap-4 hover:bg-[#fcfcfc] transition-colors group">
                        <div class="w-12 h-12 rounded-[16px] bg-gradient-to-tr from-blue-50 to-indigo-50 border border-blue-100/50 flex items-center justify-center shrink-0 shadow-sm group-hover:shadow-md transition-all">
                            <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <h4 class="text-[15px] font-semibold text-[#1d1d1f] truncate">{{ $deployment->feature?->name ?? 'Feature Update' }}</h4>
                                @if($deployment->isNew())
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100/50 text-blue-700 rounded-full uppercase tracking-wider ring-1 ring-blue-500/20">New</span>
                                @endif
                            </div>
                            <p class="text-[13px] font-medium text-[#86868b] truncate">{{ $deployment->change_summary ?? 'System enhancement successfully deployed.' }}</p>
                        </div>
                        <span class="text-[12px] text-[#86868b] font-medium whitespace-nowrap bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-100">{{ $deployment->deployed_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="px-6 py-16 flex flex-col items-center justify-center text-center h-full">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        </div>
                        <p class="text-[15px] font-semibold text-[#1d1d1f] mb-1">Up to date</p>
                        <p class="text-[13px] font-medium text-[#86868b]">No recent system changes or deployments.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ─── Support Actions ─── --}}
        <div class="space-y-6">
            {{-- Open Tickets --}}
            <div class="bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.03)] rounded-[24px] p-6 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity duration-500"></div>
                <h3 class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-5 relative">Support Center</h3>
                
                <div class="flex items-center gap-4 mb-6 relative">
                    <div class="w-14 h-14 rounded-[18px] bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100/50 flex items-center justify-center shadow-sm">
                        <span class="text-[22px] font-bold text-amber-600">{{ $openTickets }}</span>
                    </div>
                    <div>
                        <p class="text-[16px] font-bold text-[#1d1d1f] tracking-tight">Active Tickets</p>
                        <p class="text-[13px] font-medium text-[#86868b] mt-0.5">Awaiting IT response</p>
                    </div>
                </div>
                <a href="{{ route('branch.support') }}" class="relative block w-full px-4 py-3 bg-[#1d1d1f] hover:bg-[#333336] text-white text-[14px] font-semibold rounded-xl transition-all text-center shadow-md hover:shadow-lg active:scale-[0.98]">
                    Contact IT Support
                </a>
            </div>

            {{-- IT Contact Info --}}
            <div class="bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.03)] rounded-[24px] p-6">
                <h3 class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-5">IT Department Info</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-3 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-[#86868b] uppercase tracking-wide mb-0.5">Email Support</p>
                            <p class="text-[14px] text-[#1d1d1f] font-semibold truncate">{{ $itSupport['email'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-3 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-[#86868b] uppercase tracking-wide mb-0.5">Hotline</p>
                            <p class="text-[14px] text-[#1d1d1f] font-semibold truncate">{{ $itSupport['phone'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Feature Availability Table ─── --}}
    <div class="bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.03)] rounded-[24px] overflow-hidden">
        <div class="px-6 py-5 border-b border-black/[0.03] bg-white flex justify-between items-center">
            <div>
                <h3 class="text-[15px] font-bold text-[#1d1d1f] tracking-tight">Available Features</h3>
                <p class="text-[13px] font-medium text-[#86868b] mt-0.5">Features currently accessible by branch staff</p>
            </div>
        </div>

        <div class="divide-y divide-black/[0.03]">
            @forelse($features as $feature)
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-[#fcfcfc] transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-[16px] {{ $feature->availability === 'available' ? 'bg-emerald-50 border-emerald-100/50 text-emerald-500' : ($feature->availability === 'degraded' ? 'bg-amber-50 border-amber-100/50 text-amber-500' : 'bg-rose-50 border-rose-100/50 text-rose-500') }} border flex items-center justify-center shadow-sm group-hover:shadow-md transition-all shrink-0">
                            @if($feature->availability === 'available')
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @elseif($feature->availability === 'degraded')
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-[15px] font-bold text-[#1d1d1f] tracking-tight">{{ $feature->name }}</h4>
                            <div class="flex items-center gap-2.5 mt-1">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-[#515154] uppercase tracking-wide border border-gray-200/60">{{ $feature->domain }}</span>
                                @if($feature->last_used)
                                    <span class="text-[12px] font-medium text-[#86868b]">Active {{ $feature->last_used->diffForHumans() }}</span>
                                @else
                                    <span class="text-[12px] font-medium text-[#86868b]">No recent activity</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($feature->health_error)
                            <span class="text-[12px] font-medium text-rose-600 bg-rose-50 px-3 py-1.5 rounded-lg border border-rose-100/50 max-w-[220px] truncate hidden md:block">{{ $feature->health_error }}</span>
                        @endif
                        <span class="px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-full {{ $feature->availability === 'available' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : ($feature->availability === 'degraded' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60') }} shadow-sm">
                            {{ ucfirst($feature->availability) }}
                        </span>
                        <button class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 transition-colors" title="Launch feature">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                    </div>
                    <p class="text-[16px] font-semibold text-[#1d1d1f] mb-1">No Features Found</p>
                    <p class="text-[14px] font-medium text-[#86868b] max-w-sm">Please contact your IT department to publish features for this branch.</p>
                    <a href="{{ route('branch.support') }}" class="inline-block mt-6 px-6 py-2.5 bg-[#1d1d1f] text-white text-[14px] font-semibold rounded-xl hover:bg-[#333336] shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
                        Contact Support
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
