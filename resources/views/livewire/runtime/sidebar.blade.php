<div>
    @foreach($menuGroups as $parentKey => $items)
        <div class="space-y-1">
            @foreach($items as $item)
                <a href="{{ $item->route_key }}" 
                   class="group flex items-center px-4 py-2 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors duration-200">
                    <span class="mr-3 text-slate-400 group-hover:text-slate-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </span>
                    {{ $item->label }}
                </a>
            @endforeach
        </div>
    @endforeach
</div>
