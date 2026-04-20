<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-white">{{ $pageName }}</h2>
            <p class="text-sm text-slate-500 mt-1">Visual Page Builder</p>
        </div>
        <div class="flex items-center gap-3">
            @if($saveStatus === 'saved')
                <span class="text-xs text-emerald-400 bg-emerald-500/10 px-3 py-1.5 rounded-lg border border-emerald-500/20">
                    ✅ Saved
                </span>
            @endif
            <button
                id="save-page-btn"
                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-indigo-500/20"
            >
                💾 Save Page
            </button>
        </div>
    </div>

    <div
        id="page-builder"
        data-page-id="{{ $pageId }}"
        data-steps='@json($steps)'
        data-entities='@json($entities)'
        style="height: 700px; border-radius: 16px; overflow: hidden;"
        x-data
        x-on:vue-page-save.window="$wire.savePageState($event.detail.steps)"
    ></div>
</div>
