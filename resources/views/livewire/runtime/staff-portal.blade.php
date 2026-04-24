<div>
    {{-- Main Content --}}
    <div class="pt-12 pb-12 max-w-6xl mx-auto px-6">
        <header class="mb-10 fade-in-up">
            <h1 class="text-[32px] font-bold tracking-[-0.02em] text-[#1d1d1f] leading-none mb-2">Available Features</h1>
            <p class="text-[15px] font-medium text-[#86868b]">Select an operational feature below to launch its runtime.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($features as $featureVersion)
                <a href="{{ route('v3.runtime', ['featureKey' => $featureVersion->feature->key]) }}" class="group bg-white border border-black/[0.04] shadow-[0_4px_20px_rgb(0,0,0,0.02)] rounded-[24px] p-6 hover:shadow-[0_12px_30px_rgb(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden block">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-[14px] bg-gradient-to-tr from-blue-50 to-indigo-50 border border-blue-100/50 flex items-center justify-center shadow-sm mb-4">
                            <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <h3 class="text-[18px] font-bold text-[#1d1d1f] tracking-tight mb-2">{{ $featureVersion->feature->name }}</h3>
                        <p class="text-[14px] font-medium text-[#515154] leading-relaxed line-clamp-2 mb-4">{{ $featureVersion->feature->description ?? 'No description.' }}</p>
                        
                        <div class="flex items-center text-[13px] font-bold text-blue-600 group-hover:text-blue-700">
                            Launch Feature 
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 text-center bg-gray-50/50 rounded-[24px] border border-black/[0.04] border-dashed">
                    <p class="text-[16px] font-bold text-[#1d1d1f] mb-2">No Features Found</p>
                    <p class="text-[14px] text-[#86868b]">There are no published features available for execution yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
