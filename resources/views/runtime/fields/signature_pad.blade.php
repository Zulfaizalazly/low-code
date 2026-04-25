<div class="space-y-2">
    <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center bg-white">
        <p class="text-sm text-slate-400">{{ $field->placeholder ?? 'Signature capture area' }}</p>
        <input type="hidden" 
               id="{{ $field->field_key }}" 
               wire:model.defer="formData.{{ $field->field_key }}">
    </div>
</div>
