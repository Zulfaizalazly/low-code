<div class="bg-gradient-to-br from-[#f9f9fb] to-[#f5f5f7] border border-black/[0.06] rounded-[16px] p-5">
    <h4 class="text-[13px] font-bold text-[#1d1d1f] mb-4 flex items-center gap-2">
        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
        {{ $field->label }}
    </h4>
    <div class="space-y-2.5">
        @php $hasData = false; @endphp
        @foreach($this->formData as $key => $value)
            @if(!empty($value) && is_scalar($value))
                @php $hasData = true; @endphp
                <div class="flex justify-between items-center py-1.5 border-b border-black/[0.04] last:border-0">
                    <span class="text-[13px] text-[#86868b]">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                    <span class="text-[13px] font-semibold text-[#1d1d1f]">{{ $value }}</span>
                </div>
            @endif
        @endforeach
        @if(!$hasData)
            <p class="text-[13px] text-[#c7c7cc] text-center py-3">Data will appear here as you fill the form.</p>
        @endif
    </div>
</div>
