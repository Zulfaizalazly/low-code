<input type="number" 
       id="{{ $field->field_key }}" 
       wire:model.blur="formData.{{ $field->field_key }}"
       class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all"
       placeholder="{{ $field->placeholder }}">
@error('formData.' . $field->field_key)
    <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
@enderror
