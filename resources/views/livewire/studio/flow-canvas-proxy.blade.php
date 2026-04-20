<div class="h-[calc(100vh-16rem)] flex flex-col">
    <div class="flex-1 rounded-3xl overflow-hidden border border-white/10 bg-slate-900/50 shadow-2xl relative" 
         id="flow-builder-container"
         wire:ignore>
        
        <!-- The Vue Flow Island -->
        <div id="flow-canvas" 
             data-flow-id="{{ $flow->id }}"
             data-nodes="{{ json_encode($flow->nodes) }}"
             data-edges="{{ json_encode($flow->edges) }}"
             data-commands="{{ json_encode($commands ?? []) }}">
             
             <!-- Content will be injected by Vue -->
             <div class="flex items-center justify-center h-full text-slate-500">
                 Initializing Visual Canvas...
             </div>
        </div>
    </div>

    <!-- Livewire Controls -->
    <div class="mt-8 flex justify-between items-center bg-white/5 border border-white/10 p-6 rounded-2xl backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <h2 class="text-xl font-bold text-white">{{ $flow->name }}</h2>
            <span class="px-2 py-0.5 rounded-full bg-slate-800 border border-white/10 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                Flow Editor
            </span>
        </div>

        <div class="flex items-center gap-3">
            <button class="px-6 py-2.5 rounded-xl border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 transition-all text-sm font-bold">
                Reset Draft
            </button>
            <button id="save-flow-btn" 
                    class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                Update Definition
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        // Handle communication from Vue to Livewire
        window.addEventListener('vue-flow-save', (event) => {
            @this.saveFlowState(event.detail.nodes, event.detail.edges);
        });
    });
</script>
@endpush
