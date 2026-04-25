<div class="space-y-2">
    <input type="file" 
           id="{{ $field->field_key }}" 
           wire:model="formData.{{ $field->field_key }}" 
           accept="image/*"
           capture="environment"
           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all">
</div>
