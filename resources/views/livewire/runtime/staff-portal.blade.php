<div>
    <div class="fade-in-up">
        <!-- Welcome Header -->
        <div class="mb-10">
            <p class="text-[14px] font-semibold text-amber-600 mb-1">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }},</p>
            <h1 class="text-[32px] font-bold tracking-[-0.02em] text-[#1d1d1f] leading-tight">{{ auth()->user()->name ?? 'Staff' }}</h1>
            <p class="text-[15px] text-[#86868b] mt-1">Select a feature below to begin processing.</p>
        </div>

        @if(session('error'))
            <div class="mb-6 px-5 py-4 bg-rose-50 border border-rose-200/60 rounded-[16px] flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                <p class="text-[14px] text-rose-700 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Feature Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($features as $featureVersion)
                @php $isUnavailable = $featureVersion->availability === 'unavailable'; @endphp
                <div class="group relative bg-white border border-black/[0.04] shadow-[0_2px_12px_rgb(0,0,0,0.02)] rounded-[20px] overflow-hidden hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] hover:-translate-y-0.5 transition-all duration-300 {{ $isUnavailable ? 'opacity-50' : '' }}">
                    <!-- Top accent bar -->
                    <div class="h-1 bg-gradient-to-r {{ $featureVersion->availability === 'available' ? 'from-amber-400 to-orange-500' : ($featureVersion->availability === 'degraded' ? 'from-yellow-400 to-amber-500' : 'from-rose-400 to-red-500') }}"></div>

                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-11 h-11 rounded-[12px] {{ $featureVersion->availability === 'available' ? 'bg-gradient-to-br from-amber-50 to-orange-50 border-amber-100/60' : ($featureVersion->availability === 'degraded' ? 'bg-gradient-to-br from-yellow-50 to-amber-50 border-yellow-100/60' : 'bg-gradient-to-br from-rose-50 to-red-50 border-rose-100/60') }} border flex items-center justify-center shadow-sm">
                                @if(str_contains(strtolower($featureVersion->feature->key), 'pledge') && str_contains(strtolower($featureVersion->feature->key), 'new'))
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" /></svg>
                                @elseif(str_contains(strtolower($featureVersion->feature->key), 'redemption'))
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>
                                @elseif(str_contains(strtolower($featureVersion->feature->key), 'renewal'))
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                @elseif(str_contains(strtolower($featureVersion->feature->key), 'payment'))
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                @else
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                @endif
                            </div>
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $featureVersion->availability === 'available' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/50' : ($featureVersion->availability === 'degraded' ? 'bg-amber-50 text-amber-700 border border-amber-200/50' : 'bg-rose-50 text-rose-700 border border-rose-200/50') }}">
                                {{ ucfirst($featureVersion->availability) }}
                            </span>
                        </div>

                        <h3 class="text-[17px] font-bold text-[#1d1d1f] tracking-tight mb-1">{{ $featureVersion->feature->name }}</h3>
                        <span class="inline-block px-2 py-0.5 text-[10px] font-semibold bg-[#f5f5f7] text-[#86868b] rounded-[6px] uppercase tracking-wide border border-black/[0.04] mb-3">{{ $featureVersion->feature->domain }}</span>

                        @if($featureVersion->health_error)
                            <p class="text-[12px] font-medium text-rose-600 bg-rose-50 px-3 py-1.5 rounded-[10px] border border-rose-100/50 mb-4">{{ $featureVersion->health_error }}</p>
                        @endif

                        @if($isUnavailable)
                            <span class="flex items-center text-[13px] font-bold text-rose-500 mt-2">Unavailable</span>
                        @else
                            <a href="{{ route('portal.operations.launch', ['featureKey' => $featureVersion->feature->key]) }}" class="mt-2 flex items-center text-[13px] font-bold text-amber-600 group-hover:text-amber-700 transition-colors">
                                Launch
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-[20px] border border-black/[0.04] shadow-[0_2px_12px_rgb(0,0,0,0.02)]">
                    <div class="w-16 h-16 bg-[#f5f5f7] rounded-full flex items-center justify-center mx-auto mb-4 border border-black/[0.04]">
                        <svg class="w-8 h-8 text-[#c7c7cc]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                    </div>
                    <p class="text-[16px] font-bold text-[#1d1d1f] mb-1">No Features Available</p>
                    <p class="text-[14px] text-[#86868b]">Contact IT to deploy features for your branch.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
