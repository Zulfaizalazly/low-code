<div>
    <header class="mb-12">
        <h1 class="text-[40px] font-bold tracking-tight text-[#1d1d1f] mb-2 leading-tight">Runtime Monitor</h1>
        <p class="text-[17px] text-[#86868b] tracking-tight">Real-time observability into your automation engine.</p>
    </header>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="p-6 rounded-[24px] bg-white border border-[#1d1d1f]/[0.04] shadow-[0_4px_24px_rgba(0,0,0,0.02)] transition-transform hover:-translate-y-1 duration-300">
            <p class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wide mb-2">Total Executions</p>
            <p class="text-[32px] font-bold text-[#1d1d1f] tracking-tight">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="p-6 rounded-[24px] bg-white border border-emerald-500/10 shadow-[0_4px_24px_rgba(16,185,129,0.05)] transition-transform hover:-translate-y-1 duration-300">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide mb-2">Success Rate</p>
            <p class="text-[32px] font-bold text-emerald-500 tracking-tight">{{ $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 1) : 100 }}%</p>
        </div>
        <div class="p-6 rounded-[24px] bg-white border border-rose-500/10 shadow-[0_4px_24px_rgba(244,63,94,0.05)] transition-transform hover:-translate-y-1 duration-300">
            <p class="text-[11px] font-semibold text-rose-600 uppercase tracking-wide mb-2">Failures</p>
            <p class="text-[32px] font-bold text-rose-500 tracking-tight">{{ number_format($stats['failed']) }}</p>
        </div>
    </div>

    <!-- Live Feed -->
    <div class="bg-white border border-[#1d1d1f]/[0.06] shadow-[0_8px_30px_rgba(0,0,0,0.04)] rounded-[24px] overflow-hidden">
        <div class="px-8 py-5 border-b border-[#1d1d1f]/[0.04] flex justify-between items-center bg-[#f5f5f7]/50">
            <h3 class="text-[12px] font-semibold text-[#1d1d1f] uppercase tracking-wide">Execution Stream</h3>
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wide">Live</span>
            </div>
        </div>

        <div class="divide-y divide-[#1d1d1f]/[0.04]">
            @foreach($executions as $execution)
                <div class="px-8 py-5 hover:bg-[#1d1d1f]/[0.02] transition-colors group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-5">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center border shadow-sm {{ $execution->status === 'completed' ? 'border-emerald-500/20 bg-emerald-50 text-emerald-500' : 'border-rose-500/20 bg-rose-50 text-rose-500' }}">
                                @if($execution->status === 'completed')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @else
                                    <span class="font-bold text-sm">!</span>
                                @endif
                            </div>
                            
                            <div>
                                <h4 class="text-[15px] font-semibold text-[#1d1d1f]">Execution #{{ str_pad($execution->id, 6, '0', STR_PAD_LEFT) }}</h4>
                                <p class="text-[13px] text-[#86868b] mt-0.5">
                                    {{ \Carbon\Carbon::parse($execution->started_at)->diffForHumans() }} 
                                    <span class="mx-2 text-[#e5e5ea]">|</span> 
                                    Flow: <span class="text-[#1d1d1f]">{{ $execution->flow_definition_id }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Mini Trace View -->
                        <div class="flex items-center gap-2">
                            @foreach($execution->nodeLogs as $node)
                                <div title="{{ $node->node_key }}" 
                                     class="w-2 h-2 rounded-full {{ $node->status === 'completed' ? 'bg-blue-500/40' : 'bg-rose-500' }}"></div>
                            @endforeach
                        </div>

                        <div class="flex items-center gap-4">
                            @if($execution->error_message)
                                <span class="text-[12px] text-rose-600 bg-rose-50 px-2.5 py-1 rounded-md truncate max-w-xs border border-rose-100">
                                    {{ Str::limit($execution->error_message, 40) }}
                                </span>
                            @endif
                            <button class="px-4 py-2 rounded-xl hover:bg-black/5 text-[#86868b] hover:text-[#1d1d1f] transition-colors text-[13px] font-medium flex items-center gap-2">
                                Inspect Trace <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="px-8 py-5 border-t border-[#1d1d1f]/[0.04] bg-[#fcfcfc]">
            {{ $executions->links() }}
        </div>
    </div>
</div>
