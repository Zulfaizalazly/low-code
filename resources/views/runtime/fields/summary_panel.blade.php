<div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
    <h4 class="text-sm font-semibold text-slate-700 mb-3">{{ $field->label }}</h4>
    <div class="space-y-2 text-sm text-slate-600">
        @foreach($this->formData as $key => $value)
            @if(!empty($value) && is_scalar($value))
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                    <span class="font-medium text-slate-800">{{ $value }}</span>
                </div>
            @endif
        @endforeach
    </div>
</div>
