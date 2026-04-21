@section('builder-title', $pageName . ' — Page Builder')

@section('builder-actions')
    @if($saveStatus === 'saved')
        <span class="text-[11px] text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 font-medium">
            ✓ Saved
        </span>
    @endif
    <button
        id="save-page-btn"
        class="px-4 py-1.5 bg-[#007aff] hover:bg-[#0066d6] text-white text-[13px] font-medium rounded-full transition-all shadow-sm"
    >
        Save
    </button>
@endsection

<div
    wire:ignore
    id="page-builder"
    data-page-id="{{ $pageId }}"
    data-steps='@json($steps)'
    data-entities='@json($entities)'
    style="height: calc(100vh - 108px); border-radius: 14px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.06);"
    x-data
    x-on:vue-page-save.window="$wire.savePageState($event.detail.steps)"
></div>
