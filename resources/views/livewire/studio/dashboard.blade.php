<div>
    <header class="mb-12">
        <h1 class="text-4xl font-bold tracking-tight text-white mb-2">Feature Workspace</h1>
        <p class="text-slate-400">Design, build, and orchestrate your Arrahnu operating modules.</p>
    </header>

    <!-- Stats/Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Active Features</p>
            <p class="text-3xl font-bold text-white">{{ $features->count() }}</p>
        </div>
        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Build Pipeline</p>
            <p class="text-3xl font-bold text-indigo-400">{{ $features->where('status', 'draft')->count() }} Drafts</p>
        </div>
        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Health Status</p>
            <p class="text-3xl font-bold text-emerald-400">All Systems Go</p>
        </div>
    </div>

    <!-- Feature List -->
    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden backdrop-blur-sm">
        <div class="px-8 py-4 border-b border-white/10 flex justify-between items-center bg-white/5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Features Registry</h3>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition-all shadow-lg shadow-indigo-500/20">
                + Create Feature
            </button>
        </div>

        <div class="divide-y divide-white/5">
            @foreach($features as $feature)
                <div class="px-8 py-6 flex items-center justify-between hover:bg-white/5 transition-colors group">
                    <div class="flex items-center gap-6">
                        <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center border border-white/10 group-hover:border-indigo-500/50 transition-colors">
                            <!-- Icon would go here -->
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-white">{{ $feature->name }}</h4>
                            <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                                <span class="px-2 py-0.5 rounded bg-slate-800 border border-white/10 text-slate-400">{{ $feature->domain }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                                <span>{{ $feature->versions_count }} Versions</span>
                                <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                                <span>Key: {{ $feature->key }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 text-[10px] font-bold uppercase rounded-full {{ $feature->status === 'published' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20' }}">
                            {{ $feature->status }}
                        </span>
                        
                        <button class="p-2 rounded-lg hover:bg-white/10 text-slate-400 hover:text-white transition-all">
                            Configure →
                        </button>
                    </div>
                </div>
            @endforeach

            @if($features->isEmpty())
                <div class="px-8 py-20 text-center text-slate-500">
                    No features found. Start by creating your first operating module.
                </div>
            @endif
        </div>
    </div>
</div>
