<label class="inline-flex items-center gap-3 cursor-pointer">
    <input type="checkbox" 
           id="{{ $field->field_key }}" 
           wire:model.defer="formData.{{ $field->field_key }}" 
           class="w-5 h-5 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
    <span class="text-sm text-slate-700">{{ $field->placeholder ?? $field->label }}</span>
</label>
