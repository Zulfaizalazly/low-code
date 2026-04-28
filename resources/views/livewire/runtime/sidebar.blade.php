<div>
    @forelse($menuGroups as $groupLabel => $items)
        <div class="space-y-0.5">
            <div class="pt-5 pb-1.5 px-3 text-[11px] font-bold tracking-wider text-white/30 uppercase">
                {{ $groupLabel }}
            </div>

            @foreach($items as $item)
                @php
                    $href = route('portal.operations.launch', ['featureKey' => $item->feature_key]);
                    $isActive = request()->is('portal/operations/' . $item->feature_key . '*');
                @endphp
                <a href="{{ $href }}"
                   class="group flex items-center gap-3 px-3 py-2.5 text-[14px] leading-5 rounded-[12px] {{ $isActive ? 'bg-white/[0.1] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/[0.06] font-medium' }} transition-all duration-200">
                    <div class="w-7 h-7 rounded-[8px] {{ $isActive ? 'bg-[#1b6b2f] shadow-sm shadow-green-500/20' : 'bg-white/[0.08] group-hover:bg-white/[0.12]' }} flex items-center justify-center transition-all">
                        @if(str_contains(strtolower($item->label), 'pledge') && str_contains(strtolower($item->label), 'new'))
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-white/50 group-hover:text-white/70' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        @elseif(str_contains(strtolower($item->label), 'redemption'))
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-white/50 group-hover:text-white/70' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>
                        @elseif(str_contains(strtolower($item->label), 'renewal'))
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-white/50 group-hover:text-white/70' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        @elseif(str_contains(strtolower($item->label), 'payment'))
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-white/50 group-hover:text-white/70' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        @else
                            <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-white/50 group-hover:text-white/70' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        @endif
                    </div>
                    {{ $item->label }}
                </a>
            @endforeach
        </div>
    @empty
        <div class="px-4 py-6 text-center">
            <p class="text-[12px] text-white/30 font-medium">No features deployed yet.</p>
        </div>
    @endforelse
</div>
