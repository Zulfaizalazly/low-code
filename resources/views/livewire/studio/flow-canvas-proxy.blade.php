<div class="h-[calc(100vh-6rem)] flex flex-col">
    <!-- Header Controls -->
    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-semibold text-[#1d1d1f] tracking-tight">{{ $flow->name }}</h2>
            <span class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-600 border border-blue-100 text-[10px] font-bold uppercase tracking-widest">
                Flow Editor
            </span>
        </div>

        <div class="flex items-center gap-3">
            <button class="px-5 py-2 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-all text-sm font-medium tracking-wide">
                Reset Draft
            </button>
            <button id="save-flow-btn" 
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all shadow-sm tracking-wide flex items-center gap-2 disabled:opacity-50 disabled:cursor-wait">
                <span id="save-btn-spinner" class="hidden">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span id="save-btn-text">Update Definition</span>
            </button>
        </div>
    </div>

    <!-- Canvas Wrapper -->
    <div class="flex-1 rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm relative ring-1 ring-black/5" 
         id="flow-builder-container"
         wire:ignore>
        
        <!-- The Vue Flow Island -->
        <div id="flow-canvas" 
             data-flow-id="{{ $flow->id }}"
             data-version-id="{{ $flow->feature_version_id }}"
             data-flow-key="{{ $flow->key }}"
             data-nodes="{{ json_encode($flow->nodes) }}"
             data-edges="{{ json_encode($flow->edges) }}"
             data-commands="{{ json_encode($commands ?? []) }}"
             style="height: 100%; min-height: 600px; width: 100%;">
             
             <!-- Fallback shown until Vue mounts -->
             <div class="flex items-center justify-center h-full text-slate-500 bg-white">
                 <div class="text-center">
                     <div class="w-8 h-8 border-2 border-blue-400 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                     <p class="text-sm font-medium">Initializing Visual Canvas...</p>
                 </div>
             </div>
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
