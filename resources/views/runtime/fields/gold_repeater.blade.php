<div class="border border-amber-200/60 rounded-[16px] p-5 bg-gradient-to-br from-amber-50/50 to-orange-50/30">
    <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-amber-400 to-orange-500 shadow-sm shadow-amber-500/20 flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
        </div>
        <div>
            <p class="text-[14px] font-bold text-amber-900">{{ $field->label }}</p>
            <p class="text-[12px] text-amber-700/70">{{ $field->placeholder ?? 'Add gold item details for valuation' }}</p>
        </div>
    </div>
    <button type="button" class="w-full py-2.5 border-2 border-dashed border-amber-300/60 rounded-[12px] text-[13px] font-semibold text-amber-700 hover:bg-amber-100/50 transition-colors flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Add Item
    </button>
    <input type="hidden"
           id="{{ $field->field_key }}"
           wire:model.defer="formData.{{ $field->field_key }}">
</div>
