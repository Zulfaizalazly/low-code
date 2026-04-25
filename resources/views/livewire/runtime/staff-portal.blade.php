<div>
    <div class="pt-12 pb-12 max-w-6xl mx-auto px-6">
        <header class="mb-10 fade-in-up">
            <h1 class="text-[32px] font-bold tracking-[-0.02em] text-[#1d1d1f] leading-none mb-2">Available Features</h1>
            <p class="text-[15px] font-medium text-[#86868b]">Select an operational feature below to launch its runtime.</p>
        </header>

        @if(session('error'))
            <div class="mb-6 px-5 py-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                <p class="text-[14px] text-rose-700 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($features as $featureVersion)
                @php $isUnavailable = $featureVersion->availability === 'unavailable'; @endphp
                <div class="group bg-white border border-black/[0.04] shadow-[0_4px_20px_rgb(0,0,0,0.02)] rounded-[24px] p-6 hover:shadow-[0_12px_30px_rgb(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden {{ $isUnavailable ? 'opacity-60' : '' }}">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative">
                        {{-- Status icon --}}
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-[14px] {{ $featureVersion->availability === 'available' ? 'bg-gradient-to-tr from-emerald-50 to-teal-50 border-emerald-100/50' : ($featureVersion->availability === 'degraded' ? 'bg-gradient-to-tr from-amber-50 to-orange-50 border-amber-100/50' : 'bg-gradient-to-tr from-rose-50 to-red-50 border-rose-100/50') }} border flex items-center justify-center shadow-sm">
                                @if($featureVersion->availability === 'available')
                                    <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @elseif($featureVersion->availability === 'degraded')
                                    <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                                @else
                                    <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                @endif
                            </div>
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $featureVersion->availability === 'available' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/50' : ($featureVersion->availability === 'degraded' ? 'bg-amber-50 text-amber-700 border border-amber-200/50' : 'bg-rose-50 text-rose-700 border border-rose-200/50') }}">
                                {{ ucfirst($featureVersion->availability) }}
                            </span>
                        </div>

                        <h3 class="text-[18px] font-bold text-[#1d1d1f] tracking-tight mb-1">{{ $featureVersion->feature->name }}</h3>
                        <span class="inline-block px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-[#515154] rounded-md uppercase tracking-wide border border-gray-200/60 mb-3">{{ $featureVersion->feature->domain }}</span>
                        <p class="text-[14px] font-medium text-[#515154] leading-relaxed line-clamp-2 mb-4">{{ $featureVersion->feature->description ?? 'No description.' }}</p>

                        @if($featureVersion->health_error)
                            <p class="text-[12px] font-medium text-rose-600 bg-rose-50 px-3 py-1.5 rounded-lg border border-rose-100/50 mb-4">{{ $featureVersion->health_error }}</p>
                        @endif

                        @if($isUnavailable)
                            <span class="flex items-center text-[13px] font-bold text-rose-500">
                                Feature Unavailable
                            </span>
                        @else
                            <a href="{{ route('v3.runtime', ['featureKey' => $featureVersion->feature->key]) }}" class="flex items-center text-[13px] font-bold text-blue-600 group-hover:text-blue-700">
                                Launch Feature
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-[24px] border border-black/[0.04] shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                    </div>
                    <p class="text-[16px] font-bold text-[#1d1d1f] mb-2">No Features Available</p>
                    <p class="text-[14px] text-[#86868b] mb-6">Contact the IT department to deploy features for your branch.</p>
                    @if($itSupport)
                        <div class="inline-flex items-center gap-6 p-4 rounded-[16px] bg-gray-50 border border-gray-100">
                            <div class="text-left">
                                <p class="text-[11px] font-bold text-[#86868b] uppercase tracking-wider mb-0.5">Email</p>
                                <p class="text-[13px] font-bold text-[#1d1d1f]">{{ $itSupport['email'] ?? 'N/A' }}</p>
                            </div>
                            <div class="w-px h-8 bg-gray-200"></div>
                            <div class="text-left">
                                <p class="text-[11px] font-bold text-[#86868b] uppercase tracking-wider mb-0.5">Hotline</p>
                                <p class="text-[13px] font-bold text-[#1d1d1f]">{{ $itSupport['phone'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
