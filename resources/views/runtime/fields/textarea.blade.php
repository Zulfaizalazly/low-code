<textarea
       id="{{ $field->field_key }}"
       wire:model.defer="formData.{{ $field->field_key }}"
       placeholder="{{ $field->placeholder ?? '' }}"
       rows="3"
       class="block w-full px-4 py-3 rounded-[14px] border border-black/[0.08] bg-[#f9f9fb] focus:bg-white focus:border-amber-400 focus:ring-4 focus:ring-amber-500/10 transition-all text-[15px] text-[#1d1d1f] placeholder:text-[#c7c7cc] resize-none"></textarea>
