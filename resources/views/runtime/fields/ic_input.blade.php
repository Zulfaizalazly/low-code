<div class="relative">
    <input type="text"
           id="{{ $field->field_key }}"
           wire:model.defer="formData.{{ $field->field_key }}"
           placeholder="{{ $field->placeholder ?? '901231-14-5678' }}"
           maxlength="14"
           inputmode="numeric"
           class="block w-full px-4 py-3 rounded-[14px] border border-black/[0.08] bg-[#f9f9fb] focus:bg-white focus:border-green-400 focus:ring-4 focus:ring-green-500/10 transition-all text-[15px] text-[#1d1d1f] font-mono tracking-widest placeholder:text-[#c7c7cc] placeholder:font-sans placeholder:tracking-normal">
    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
        <svg class="w-5 h-5 text-[#c7c7cc]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
    </div>
</div>
