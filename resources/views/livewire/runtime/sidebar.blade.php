<div>
    @foreach($menuGroups as $parentKey => $items)
        <div class="space-y-1">
            @foreach($items as $item)
                <a href="{{ $item->route_key }}" 
                   class="group flex items-center px-4 py-2 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors duration-200">
                    @if($item->icon)
                        <span class="mr-3 text-slate-400 group-hover:text-slate-500">
                            <!-- Icon logic would go here -->
                        </span>
                    @endif
                    {{ $item->label }}
                </a>
            @endforeach
        </div>
    @endforeach
</div>
