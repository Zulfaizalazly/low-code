<div>
    <header class="mb-12">
        <h1 class="text-4xl font-bold tracking-tight text-white mb-2">Runtime Monitor</h1>
        <p class="text-slate-400">Real-time observability into your automation engine.</p>
    </header>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Total Executions</p>
            <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="p-6 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 backdrop-blur-sm">
            <p class="text-[10px] font-bold text-emerald-500/50 uppercase tracking-widest mb-1">Success Rate</p>
            <p class="text-2xl font-bold text-emerald-400">{{ $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 1) : 100 }}%</p>
        </div>
        <div class="p-6 rounded-2xl bg-rose-500/5 border border-rose-500/20 backdrop-blur-sm">
            <p class="text-[10px] font-bold text-rose-500/50 uppercase tracking-widest mb-1">Failures</p>
            <p class="text-2xl font-bold text-rose-400">{{ number_format($stats['failed']) }}</p>
        </div>
    </div>

    <!-- Live Feed -->
    <div class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden backdrop-blur-xl">
        <div class="px-8 py-5 border-b border-white/10 flex justify-between items-center bg-white/5">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Execution Stream</h3>
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Live</span>
            </div>
        </div>

        <div class="divide-y divide-white/5">
            @foreach($executions as $execution)
                <div class="px-8 py-6 hover:bg-white/5 transition-all group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center border {{ $execution->status === 'completed' ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400' : 'border-rose-500/20 bg-rose-500/10 text-rose-400' }}">
                                @if($execution->status === 'completed')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                @else
                                    <span class="font-bold text-sm">!</span>
                                @endif
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-bold text-white">Execution #{{ str_pad($execution->id, 6, '0', STR_PAD_LEFT) }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $execution->started_at->diffForHumans() }} 
                                    <span class="mx-2 opacity-30">|</span> 
                                    Flow: <span class="text-slate-300">{{ $execution->flow_definition_id }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Mini Trace View -->
                        <div class="flex items-center gap-2">
                            @foreach($execution->nodeLogs as $node)
                                <div title="{{ $node->node_key }}" 
                                     class="w-2 h-2 rounded-full {{ $node->status === 'completed' ? 'bg-indigo-500/50' : 'bg-rose-500' }}"></div>
                            @endforeach
                        </div>

                        <div class="flex items-center gap-4">
                            @if($execution->error_message)
                                <span class="text-[10px] text-rose-400 font-mono bg-rose-400/10 px-2 py-1 rounded truncate max-w-xs">
                                    {{ Str::limit($execution->error_message, 40) }}
                                </span>
                            @endif
                            <button class="p-2 rounded-lg hover:bg-white/10 text-slate-400 hover:text-white transition-all text-xs font-bold uppercase tracking-widest">
                                Inspect Trace →
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="px-8 py-4 border-t border-white/10 bg-white/5">
            {{ $executions->links() }}
        </div>
    </div>
</div>
