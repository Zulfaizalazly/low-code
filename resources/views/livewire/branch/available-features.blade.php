<div class="max-w-6xl mx-auto">
    <header class="mb-10 fade-in-up">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-[32px] font-bold tracking-[-0.02em] text-[#1d1d1f] leading-none">Available Features</h1>
                <p class="text-[15px] font-medium text-[#86868b]">Features available for your branch staff to use.</p>
            </div>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-[#86868b] group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search features..."
                    class="w-72 pl-10 pr-4 py-3 rounded-[16px] border border-black/[0.06] bg-white/60 backdrop-blur-md text-[14px] font-medium text-[#1d1d1f] placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm"
                >
            </div>
        </div>
    </header>

    {{-- ─── Summary Strip ─── --}}
    <div class="grid grid-cols-3 gap-5 mb-10">
        <div class="p-6 rounded-[24px] bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.05)] hover:-translate-y-0.5 transition-all duration-500 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-b from-gray-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative">
                <p class="text-[12px] font-bold text-[#86868b] uppercase tracking-wider mb-2">Total Features</p>
                <p class="text-[32px] font-bold text-[#1d1d1f] tracking-tight leading-none">{{ $totalFeatures }}</p>
            </div>
        </div>
        <div class="p-6 rounded-[24px] bg-white border border-emerald-500/10 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.05)] hover:-translate-y-0.5 transition-all duration-500 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-b from-emerald-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative">
                <p class="text-[12px] font-bold text-emerald-600 uppercase tracking-wider mb-2">Available</p>
                <p class="text-[32px] font-bold text-emerald-500 tracking-tight leading-none">{{ $availableCount }}</p>
            </div>
        </div>
        <div class="p-6 rounded-[24px] bg-white border border-{{ $issueCount > 0 ? 'rose' : 'black' }}/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.05)] hover:-translate-y-0.5 transition-all duration-500 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-b from-{{ $issueCount > 0 ? 'rose' : 'gray' }}-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative">
                <p class="text-[12px] font-bold text-{{ $issueCount > 0 ? 'rose' : '[#86868b]' }}-600 uppercase tracking-wider mb-2">Issues</p>
                <p class="text-[32px] font-bold text-{{ $issueCount > 0 ? 'rose' : '[#1d1d1f]' }}-500 tracking-tight leading-none">{{ $issueCount }}</p>
            </div>
        </div>
    </div>

    {{-- ─── Feature Cards ─── --}}
    <div class="space-y-5">
        @forelse($features as $feature)
            <div x-data="{ expanded: false }" class="group bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.02)] rounded-[24px] p-6 hover:shadow-[0_20px_40px_rgb(0,0,0,0.05)] hover:-translate-y-0.5 transition-all duration-500 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-white/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                <div class="flex items-start justify-between relative">
                    <div class="flex items-start gap-5">
                        {{-- Status icon --}}
                        <div class="w-14 h-14 rounded-[16px] {{ $feature->availability === 'available' ? 'bg-gradient-to-tr from-emerald-50 to-teal-50 border-emerald-100/50 shadow-sm' : ($feature->availability === 'degraded' ? 'bg-gradient-to-tr from-amber-50 to-orange-50 border-amber-100/50 shadow-sm' : 'bg-gradient-to-tr from-rose-50 to-red-50 border-rose-100/50 shadow-sm') }} border flex items-center justify-center shrink-0 mt-0.5">
                            @if($feature->availability === 'available')
                                <svg class="w-7 h-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            @elseif($feature->availability === 'degraded')
                                <svg class="w-7 h-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                            @else
                                <svg class="w-7 h-7 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center gap-3 mb-1.5">
                                <h3 class="text-[18px] font-bold text-[#1d1d1f] tracking-tight">{{ $feature->name }}</h3>
                                @if($feature->is_new)
                                    <span class="px-2.5 py-1 text-[10px] font-bold bg-blue-50 text-blue-700 rounded-full uppercase tracking-widest animate-pulse border border-blue-200/50">New</span>
                                @endif
                                @if($feature->version_no)
                                    <span class="px-2.5 py-1 text-[10px] font-bold bg-gray-50 text-[#86868b] rounded-md border border-gray-100">v{{ $feature->version_no }}</span>
                                @endif
                            </div>
                            <p class="text-[14px] font-medium text-[#515154] mb-4 leading-relaxed max-w-3xl">{{ $feature->description ?? 'No description available.' }}</p>

                            <div class="flex items-center gap-5 flex-wrap">
                                <span class="px-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wider bg-gray-50 text-[#515154] border border-gray-100">{{ $feature->domain }}</span>

                                @if($feature->last_used)
                                    <span class="text-[12px] font-medium text-[#86868b] flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Last used {{ \Carbon\Carbon::parse($feature->last_used)->diffForHumans() }}
                                    </span>
                                @endif

                                @if($feature->last_deployed)
                                    <span class="text-[12px] font-medium text-[#86868b] flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                        Deployed {{ \Carbon\Carbon::parse($feature->last_deployed)->diffForHumans() }}
                                    </span>
                                @endif

                                <span class="text-[12px] font-medium text-[#86868b] flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                    {{ $feature->week_usage }} uses this week
                                </span>

                                {{-- View Details toggle --}}
                                <button
                                    @click="expanded = !expanded"
                                    class="text-[12px] font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors"
                                >
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    <span x-text="expanded ? 'Hide Details' : 'View Details'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-3">
                        <span class="px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-full shadow-sm {{ $feature->availability === 'available' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/50' : ($feature->availability === 'degraded' ? 'bg-amber-50 text-amber-700 border border-amber-200/50' : 'bg-rose-50 text-rose-700 border border-rose-200/50') }}">
                            {{ ucfirst($feature->availability) }}
                        </span>

                        @if($feature->health_error)
                            <span class="text-[12px] font-medium text-rose-600 bg-rose-50 px-3 py-1.5 rounded-[10px] border border-rose-100 max-w-[250px] text-right">
                                {{ $feature->health_error }}
                            </span>
                        @endif

                        @if($feature->availability !== 'available')
                            <a href="{{ route('branch.support') }}" class="text-[12px] font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 mt-1">
                                Report to IT
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Expandable documentation panel --}}
                <div
                    x-show="expanded"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="mt-4 ml-[76px] relative"
                >
                    <div class="p-4 rounded-[16px] bg-gray-50 border border-gray-100 text-[13px] font-medium text-[#515154] leading-relaxed">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <span class="text-[11px] font-bold text-[#86868b] uppercase tracking-wider">Feature Documentation</span>
                        </div>
                        {{ $feature->description ?? 'No documentation available for this feature.' }}
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.02)] rounded-[32px] p-16 text-center flex flex-col items-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6 border border-gray-100 shadow-sm">
                    <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                </div>
                <p class="text-[20px] font-bold text-[#1d1d1f] tracking-tight mb-2">No features available</p>
                <p class="text-[15px] font-medium text-[#86868b] max-w-md mb-8">Contact the IT department to deploy features for your branch. Once deployed, they will appear here.</p>
                <div class="inline-flex flex-col md:flex-row items-center gap-6 p-6 rounded-[20px] bg-gray-50 border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center border border-gray-100">
                            <svg class="w-5 h-5 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="text-left">
                            <p class="text-[11px] font-bold text-[#86868b] uppercase tracking-wider mb-0.5">Email Support</p>
                            <p class="text-[13px] font-bold text-[#1d1d1f]">{{ $itSupport['email'] }}</p>
                        </div>
                    </div>
                    <div class="hidden md:block w-px h-10 bg-gray-200"></div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center border border-gray-100">
                            <svg class="w-5 h-5 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </div>
                        <div class="text-left">
                            <p class="text-[11px] font-bold text-[#86868b] uppercase tracking-wider mb-0.5">Hotline</p>
                            <p class="text-[13px] font-bold text-[#1d1d1f]">{{ $itSupport['phone'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
