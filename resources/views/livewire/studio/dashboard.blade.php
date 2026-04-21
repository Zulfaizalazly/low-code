<div>
    <header class="mb-12">
        <h1 class="text-[40px] font-bold tracking-tight text-[#1d1d1f] mb-2 leading-tight">Feature Workspace</h1>
        <p class="text-[17px] text-[#86868b] tracking-tight">Design, build, and orchestrate your Arrahnu operating modules.</p>
    </header>

    {{-- Flash success message --}}
    @if(session('success'))
        <div class="mb-6 px-5 py-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <p class="text-[14px] text-emerald-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Stats/Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="p-6 rounded-[24px] bg-white border border-[#1d1d1f]/[0.04] shadow-[0_4px_24px_rgba(0,0,0,0.02)] transition-transform hover:-translate-y-1 duration-300">
            <p class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wide mb-2">Active Features</p>
            <p class="text-[32px] font-bold text-[#1d1d1f] tracking-tight">{{ $features->count() }}</p>
        </div>
        <div class="p-6 rounded-[24px] bg-white border border-[#1d1d1f]/[0.04] shadow-[0_4px_24px_rgba(0,0,0,0.02)] transition-transform hover:-translate-y-1 duration-300">
            <p class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wide mb-2">Build Pipeline</p>
            <p class="text-[32px] font-bold text-blue-600 tracking-tight">{{ $features->where('status', 'draft')->count() }} Drafts</p>
        </div>
        <div class="p-6 rounded-[24px] bg-white border border-[#1d1d1f]/[0.04] shadow-[0_4px_24px_rgba(0,0,0,0.02)] transition-transform hover:-translate-y-1 duration-300">
            <p class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wide mb-2">AI Usage MTD</p>
            <p class="text-[32px] font-bold tracking-tight {{ $budget_used_percent > 80 ? 'text-orange-500' : 'text-[#1d1d1f]' }}">
                ${{ number_format($mtd_ai_cost, 2) }}
            </p>
            <div class="mt-3 w-full bg-[#f5f5f7] rounded-full h-1.5 overflow-hidden">
                <div class="h-full {{ $budget_used_percent > 90 ? 'bg-rose-500' : ($budget_used_percent > 70 ? 'bg-orange-500' : 'bg-blue-500') }}" 
                     style="width: {{ min($budget_used_percent, 100) }}%"></div>
            </div>
        </div>
        <div class="p-6 rounded-[24px] bg-white border border-emerald-500/10 shadow-[0_4px_24px_rgba(16,185,129,0.05)] transition-transform hover:-translate-y-1 duration-300">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide mb-2">Health Status</p>
            <p class="text-[32px] font-bold text-emerald-500 tracking-tight">All Systems Go</p>
        </div>
    </div>

    <!-- Feature List -->
    <div class="bg-white border border-[#1d1d1f]/[0.06] shadow-[0_8px_30px_rgba(0,0,0,0.04)] rounded-[24px] overflow-hidden">
        <div class="px-8 py-5 border-b border-[#1d1d1f]/[0.04] flex justify-between items-center bg-[#f5f5f7]/50">
            <h3 class="text-[12px] font-semibold text-[#1d1d1f] uppercase tracking-wide">Features Registry</h3>
            <button 
                wire:click="openCreateModal"
                class="px-4 py-2 bg-[#1d1d1f] hover:bg-[#434346] text-white text-[13px] font-semibold rounded-xl transition-all shadow-md shadow-black/10 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                New Feature
            </button>
        </div>

        <div class="divide-y divide-[#1d1d1f]/[0.04]">
            @foreach($features as $feature)
                @php
                    $latestVersion = $feature->versions->first();
                    $primaryFlow = $latestVersion ? $latestVersion->flows->first() : null;
                    $primaryPage = $latestVersion ? $latestVersion->pages->first() : null;
                @endphp
                <div class="px-8 py-6 flex items-center justify-between hover:bg-[#1d1d1f]/[0.02] transition-colors group">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-gray-100 to-gray-200 border border-gray-300/50 shadow-inner flex items-center justify-center text-gray-500 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <div>
                            <h4 class="text-[17px] font-semibold text-[#1d1d1f] tracking-tight">{{ $feature->name }}</h4>
                            <div class="flex items-center gap-3 text-[13px] text-[#86868b] mt-1 font-medium">
                                <span class="px-2.5 py-0.5 rounded-md bg-[#1d1d1f]/5 border border-[#1d1d1f]/10 text-[#1d1d1f]">{{ $feature->domain }}</span>
                                <span class="w-1 h-1 rounded-full bg-[#d2d2d7]"></span>
                                <span>{{ $feature->versions_count }} Versions</span>
                                <span class="w-1 h-1 rounded-full bg-[#d2d2d7]"></span>
                                <span>Key: <span class="font-mono text-blue-600">{{ $feature->key }}</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 text-[11px] font-bold uppercase tracking-wide rounded-full {{ $feature->status === 'published' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
                            {{ $feature->status }}
                        </span>
                        
                        <!-- ENGINE BUILDER ACTIONS -->
                        <div class="flex items-center gap-2 border-l border-[#1d1d1f]/10 pl-4 ml-2">
                            @if($primaryFlow)
                                <a href="{{ route('studio.flow-canvas', $primaryFlow->id) }}" 
                                   class="px-4 py-2 bg-white border border-[#1d1d1f]/10 shadow-[0_2px_8px_rgba(0,0,0,0.04)] rounded-xl hover:bg-[#fcfcfc] hover:border-[#1d1d1f]/20 text-[#1d1d1f] text-[13px] font-medium transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Flow Editor
                                </a>
                            @endif

                            @if($primaryPage && $latestVersion)
                                <a href="{{ route('studio.page-builder', ['featureVersionId' => $latestVersion->id, 'pageId' => $primaryPage->id]) }}" 
                                   class="px-4 py-2 bg-white border border-[#1d1d1f]/10 shadow-[0_2px_8px_rgba(0,0,0,0.04)] rounded-xl hover:bg-[#fcfcfc] hover:border-[#1d1d1f]/20 text-[#1d1d1f] text-[13px] font-medium transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z" /></svg> UI Builder
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            @if($features->isEmpty())
                <div class="px-8 py-20 text-center flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" /></svg>
                    </div>
                    <p class="text-[15px] text-[#86868b] font-medium mb-4">No features yet. Build your first operating module.</p>
                    <button wire:click="openCreateModal" class="px-5 py-2.5 bg-[#1d1d1f] text-white text-[14px] font-semibold rounded-xl hover:bg-[#434346] transition-all">
                        + Create Your First Feature
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Create Feature Modal ─── --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center" wire:keydown.escape="$set('showCreateModal', false)">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>

        {{-- Modal Panel --}}
        <div class="relative bg-white rounded-[28px] shadow-[0_32px_80px_rgba(0,0,0,0.12)] w-full max-w-lg mx-4 overflow-hidden">
            <div class="px-8 pt-8 pb-6 border-b border-[#1d1d1f]/[0.06]">
                <h2 class="text-[22px] font-bold text-[#1d1d1f] tracking-tight">New Operating Feature</h2>
                <p class="text-[14px] text-[#86868b] mt-1">A blank Flow and Page will be bootstrapped automatically.</p>
            </div>

            <div class="px-8 py-6 space-y-5">
                {{-- Blueprint / Template Selector --}}
                <div>
                    <label class="block text-[12px] font-semibold text-[#1d1d1f] uppercase tracking-wide mb-2">Industry Blueprint</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center p-3 rounded-xl border cursor-pointer transition-all {{ $selectedBlueprint === 'blank' ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-[#1d1d1f]/10 bg-white hover:bg-[#1d1d1f]/[0.02]' }}">
                            <input type="radio" wire:model.live="selectedBlueprint" value="blank" class="sr-only">
                            <div>
                                <p class="text-[14px] font-semibold text-[#1d1d1f]">Scratch / Blank Feature</p>
                                <p class="text-[12px] text-[#86868b]">Empty canvas, no presets.</p>
                            </div>
                        </label>

                        <label class="relative flex items-center p-3 rounded-xl border cursor-pointer transition-all {{ $selectedBlueprint === 'pledge_intake' ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500' : 'border-[#1d1d1f]/10 bg-white hover:bg-[#1d1d1f]/[0.02]' }}">
                            <input type="radio" wire:model.live="selectedBlueprint" value="pledge_intake" class="sr-only">
                            <div class="absolute top-[-10px] right-2 bg-emerald-100 text-emerald-800 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">SKM 2026</div>
                            <div>
                                <p class="text-[14px] font-semibold text-[#1d1d1f]">Standard Pledge Intake</p>
                                <p class="text-[12px] text-[#86868b]">AMLA, Valuation, Surat Pajak.</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Feature Name --}}
                <div>
                    <label class="block text-[12px] font-semibold text-[#1d1d1f] uppercase tracking-wide mb-2">Feature Name</label>
                    <input 
                        type="text" 
                        wire:model.live="newFeatureName"
                        placeholder="e.g. New Pledge Intake"
                        class="w-full px-4 py-3 rounded-xl border border-[#1d1d1f]/10 bg-[#fcfcfc] text-[15px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 transition-all"
                        autofocus
                    >
                    @error('newFeatureName')<p class="text-[12px] text-rose-500 mt-1.5">{{ $message }}</p>@enderror
                </div>

                {{-- Feature Key (auto-generated) --}}
                <div>
                    <label class="block text-[12px] font-semibold text-[#1d1d1f] uppercase tracking-wide mb-2">
                        Unique Key 
                        <span class="text-[#86868b] font-normal normal-case tracking-normal">(auto-generated, editable)</span>
                    </label>
                    <div class="flex items-center gap-2 px-4 py-3 rounded-xl border border-[#1d1d1f]/10 bg-[#f5f5f7]">
                        <span class="text-[#86868b] text-[14px] shrink-0">f/</span>
                        <input 
                            type="text" 
                            wire:model="newFeatureKey"
                            placeholder="new-pledge-intake"
                            class="flex-1 bg-transparent text-[15px] font-mono text-blue-600 focus:outline-none"
                        >
                    </div>
                    @error('newFeatureKey')<p class="text-[12px] text-rose-500 mt-1.5">{{ $message }}</p>@enderror
                </div>

                {{-- Domain --}}
                <div>
                    <label class="block text-[12px] font-semibold text-[#1d1d1f] uppercase tracking-wide mb-2">Business Domain</label>
                    <select 
                        wire:model="newFeatureDomain"
                        class="w-full px-4 py-3 rounded-xl border border-[#1d1d1f]/10 bg-[#fcfcfc] text-[15px] text-[#1d1d1f] focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 transition-all appearance-none"
                    >
                        <option>General</option>
                        <option>Facility</option>
                        <option>Customer</option>
                        <option>Finance</option>
                        <option>Operations</option>
                        <option>Compliance</option>
                        <option>HR</option>
                    </select>
                    @error('newFeatureDomain')<p class="text-[12px] text-rose-500 mt-1.5">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="px-8 pb-8 flex justify-end gap-3">
                <button 
                    wire:click="$set('showCreateModal', false)"
                    class="px-5 py-2.5 rounded-xl border border-[#1d1d1f]/10 text-[14px] font-medium text-[#86868b] hover:text-[#1d1d1f] hover:bg-[#f5f5f7] transition-all">
                    Cancel
                </button>
                <button 
                    wire:click="createFeature"
                    wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-[#1d1d1f] hover:bg-[#434346] text-white text-[14px] font-semibold rounded-xl transition-all shadow-md shadow-black/10 flex items-center gap-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="createFeature">Create Feature →</span>
                    <span wire:loading wire:target="createFeature" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Creating...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
