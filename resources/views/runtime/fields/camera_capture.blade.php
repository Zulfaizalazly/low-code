<div class="border-2 border-dashed border-black/[0.08] rounded-[16px] p-6 text-center bg-[#f9f9fb] hover:bg-[#f5f5f7] transition-colors cursor-pointer group">
    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm border border-black/[0.06] group-hover:shadow-md transition-shadow">
        <svg class="w-6 h-6 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
    </div>
    <p class="text-[13px] font-semibold text-[#515154]">Tap to capture photo</p>
    <p class="text-[11px] text-[#86868b] mt-1">{{ $field->config['min_photos'] ?? 1 }} photo(s) required</p>
    <input type="file"
           id="{{ $field->field_key }}"
           wire:model="formData.{{ $field->field_key }}"
           accept="image/*"
           capture="environment"
           class="hidden">
</div>
