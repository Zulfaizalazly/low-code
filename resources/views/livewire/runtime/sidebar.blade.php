<div>
    @forelse($menuGroups as $groupLabel => $items)
        <div class="space-y-0.5">
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold tracking-wider text-white/25 uppercase">
                {{ $groupLabel }}
            </div>

            @foreach($items as $item)
                @php
                    $href = route('portal.operations.launch', ['featureKey' => $item->feature_key]);
                    $isActive = request()->is('portal/operations/' . $item->feature_key . '*');
                @endphp
                <a href="{{ $href }}"
                   class="group flex items-center gap-3 px-3 py-2 text-[13px] leading-5 rounded-lg {{ $isActive ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-all duration-200">
                    <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-[#2ecc71]' : 'bg-white/20 group-hover:bg-white/40' }} transition-colors shrink-0"></span>
                    {{ $item->label }}
                </a>
            @endforeach
        </div>
    @empty
        <div class="px-4 py-6 text-center">
            <p class="text-[11px] text-white/30 font-medium">No features deployed yet.</p>
        </div>
    @endforelse
</div>
