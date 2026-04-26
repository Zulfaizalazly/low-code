<div class="relative">
    <input type="text"
           id="{{ $field->field_key }}"
           wire:model.defer="formData.{{ $field->field_key }}"
           placeholder="{{ $field->placeholder ?? 'Scan or enter code' }}"
           class="block w-full px-4 py-3 pr-12 rounded-[14px] border border-black/[0.08] bg-[#f9f9fb] focus:bg-white focus:border-amber-400 focus:ring-4 focus:ring-amber-500/10 transition-all text-[15px] text-[#1d1d1f] font-mono placeholder:font-sans placeholder:text-[#c7c7cc]">
    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
        <div class="w-8 h-8 rounded-[8px] bg-amber-50 border border-amber-200/50 flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
        </div>
    </div>
</div>
