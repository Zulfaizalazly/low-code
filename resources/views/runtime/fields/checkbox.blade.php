<label class="inline-flex items-start gap-3 cursor-pointer group">
    <input type="checkbox"
           id="{{ $field->field_key }}"
           wire:model.defer="formData.{{ $field->field_key }}"
           class="w-5 h-5 mt-0.5 rounded-[6px] border-black/[0.15] text-green-500 shadow-sm focus:ring-green-500/20 focus:ring-4 transition-all">
    <span class="text-[14px] text-[#515154] group-hover:text-[#1d1d1f] transition-colors leading-relaxed">{{ $field->placeholder ?? $field->label }}</span>
</label>
