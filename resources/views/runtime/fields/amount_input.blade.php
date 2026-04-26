<div class="relative">
    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
        <span class="text-[14px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-[6px]">RM</span>
    </div>
    <input type="number"
           step="0.01"
           id="{{ $field->field_key }}"
           wire:model.defer="formData.{{ $field->field_key }}"
           placeholder="0.00"
           class="block w-full pl-16 pr-4 py-3 rounded-[14px] border border-black/[0.08] bg-[#f9f9fb] focus:bg-white focus:border-amber-400 focus:ring-4 focus:ring-amber-500/10 transition-all text-[15px] text-[#1d1d1f] font-semibold tabular-nums placeholder:text-[#c7c7cc] placeholder:font-normal">
</div>
