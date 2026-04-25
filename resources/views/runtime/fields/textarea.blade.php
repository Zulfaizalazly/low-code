<textarea 
       id="{{ $field->field_key }}" 
       wire:model.defer="formData.{{ $field->field_key }}" 
       placeholder="{{ $field->placeholder ?? '' }}"
       rows="4"
       class="block w-full px-4 py-3 rounded-xl border border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-slate-900 sm:text-sm"></textarea>
