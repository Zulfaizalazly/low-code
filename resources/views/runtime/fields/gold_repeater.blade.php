<div class="space-y-3">
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <p class="text-sm text-amber-700 font-medium">{{ $field->label }}</p>
        <p class="text-xs text-amber-500 mt-1">{{ $field->placeholder ?? 'Add gold item details' }}</p>
    </div>
    <input type="hidden" 
           id="{{ $field->field_key }}" 
           wire:model.defer="formData.{{ $field->field_key }}">
</div>
