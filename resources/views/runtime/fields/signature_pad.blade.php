<div class="border-2 border-dashed border-black/[0.08] rounded-[16px] p-8 text-center bg-[#f9f9fb] hover:bg-[#f5f5f7] transition-colors cursor-pointer group">
    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm border border-black/[0.06] group-hover:shadow-md transition-shadow">
        <svg class="w-6 h-6 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
    </div>
    <p class="text-[13px] font-semibold text-[#515154]">{{ $field->placeholder ?? 'Tap to sign' }}</p>
    <p class="text-[11px] text-[#86868b] mt-1">Draw your signature in the area above</p>
    <input type="hidden"
           id="{{ $field->field_key }}"
           wire:model.defer="formData.{{ $field->field_key }}">
</div>
