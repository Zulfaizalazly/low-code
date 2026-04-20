<div class="relative rounded-xl shadow-sm">
    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
        <span class="text-slate-500 sm:text-sm font-bold">RM</span>
    </div>
    <input type="number" 
           step="0.01"
           id="{{ $field->field_key }}" 
           wire:model.defer="formData.{{ $field->field_key }}" 
           placeholder="0.00"
           class="block w-full pl-12 pr-4 py-3 rounded-xl border border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-slate-900 sm:text-sm font-medium">
</div>
