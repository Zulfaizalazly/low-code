<div>
    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 shadow-sm flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <div>
                <h1 class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">Audit Logs</h1>
                <p class="text-[14px] text-[#86868b] font-medium">Monitor Branch Manager activity when operating in Staff View mode.</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total Events Today --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-[#f5f5f7] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#515154]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Today</span>
            </div>
            <p class="text-[32px] font-bold tracking-tight text-[#1d1d1f] leading-none">{{ $statsTotal }}</p>
            <p class="text-[13px] text-[#86868b] mt-1">Total Events</p>
        </div>

        {{-- Staff View Toggles --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-600">Staff View</span>
            </div>
            <p class="text-[32px] font-bold tracking-tight text-[#1d1d1f] leading-none">{{ $statsStaffViews }}</p>
            <p class="text-[13px] text-[#86868b] mt-1">View Toggles</p>
        </div>

        {{-- Feature Executions --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                </div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-red-600">Elevated</span>
            </div>
            <p class="text-[32px] font-bold tracking-tight text-[#1d1d1f] leading-none">{{ $statsFeatureExecs }}</p>
            <p class="text-[13px] text-[#86868b] mt-1">Feature Executions</p>
        </div>

        {{-- Unique Users --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-blue-600">Users</span>
            </div>
            <p class="text-[32px] font-bold tracking-tight text-[#1d1d1f] leading-none">{{ $statsUniqueUsers }}</p>
            <p class="text-[13px] text-[#86868b] mt-1">Unique Managers</p>
        </div>
    </div>

    {{-- Filters Bar --}}
    <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[240px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by user, action, or description..." 
                    class="w-full pl-10 pr-4 py-2.5 text-[14px] bg-[#f5f5f7] border-0 rounded-xl text-[#1d1d1f] placeholder-[#86868b] focus:ring-2 focus:ring-blue-500/30 focus:bg-white transition-all"
                    id="audit-search-input"
                >
            </div>

            {{-- Action Filter --}}
            <select 
                wire:model.live="filterAction" 
                class="px-4 py-2.5 text-[14px] bg-[#f5f5f7] border-0 rounded-xl text-[#1d1d1f] focus:ring-2 focus:ring-blue-500/30 focus:bg-white transition-all cursor-pointer appearance-none min-w-[200px]"
                id="audit-action-filter"
            >
                @foreach($actionTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            {{-- Date Range Filter --}}
            <div class="flex items-center bg-[#f5f5f7] rounded-xl p-1 gap-0.5">
                @foreach(['24h' => '24h', '7d' => '7 Days', '30d' => '30 Days', 'all' => 'All'] as $val => $label)
                    <button 
                        wire:click="$set('filterDateRange', '{{ $val }}')"
                        class="px-3 py-1.5 text-[13px] font-medium rounded-lg transition-all {{ $filterDateRange === $val ? 'bg-white text-[#1d1d1f] shadow-sm' : 'text-[#86868b] hover:text-[#515154]' }}"
                        id="audit-date-filter-{{ $val }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Audit Logs Table --}}
    <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] shadow-sm overflow-hidden">
        @if($logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-[#f5f5f7] flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-[#86868b]/60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h3 class="text-[16px] font-semibold text-[#1d1d1f] mb-1">No Audit Events Found</h3>
                <p class="text-[14px] text-[#86868b] max-w-sm">
                    @if($search || $filterAction)
                        No events match your current filters. Try adjusting your search criteria.
                    @else
                        No audit events have been logged yet. Events will appear here when Branch Managers toggle Staff View or execute features.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#1d1d1f]/[0.06]">
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Timestamp</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">User</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Action</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Description</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1d1d1f]/[0.04]">
                        @foreach($logs as $log)
                            <tr class="hover:bg-[#f5f5f7]/50 transition-colors group" wire:key="audit-{{ $log->id }}">
                                {{-- Timestamp --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-[13px] font-medium text-[#1d1d1f]">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-[12px] text-[#86868b]">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>

                                {{-- User --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#e5e5ea] to-[#d1d1d6] flex items-center justify-center text-[12px] font-bold text-[#515154] shrink-0">
                                            {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-[13px] font-medium text-[#1d1d1f]">{{ $log->user?->name ?? 'Unknown' }}</div>
                                            <div class="text-[12px] text-[#86868b]">{{ $log->user?->email ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Action Badge --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ \App\Livewire\Studio\AuditLogs::actionColor($log->action) }}">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ \App\Livewire\Studio\AuditLogs::actionIcon($log->action) }}" /></svg>
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>
                                </td>

                                {{-- Description --}}
                                <td class="px-6 py-4">
                                    <p class="text-[13px] text-[#515154] max-w-md truncate" title="{{ $log->description }}">{{ $log->description }}</p>
                                </td>

                                {{-- Payload Details (Expandable) --}}
                                <td class="px-6 py-4" x-data="{ showPayload: false }">
                                    @if($log->payload)
                                        <button 
                                            @click="showPayload = !showPayload"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-medium text-[#515154] bg-[#f5f5f7] hover:bg-[#e5e5ea] rounded-lg transition-all"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                                            <span x-text="showPayload ? 'Hide' : 'View'"></span>
                                        </button>

                                        {{-- Payload Modal Overlay --}}
                                        <div 
                                            x-show="showPayload" 
                                            x-transition.opacity
                                            @click.away="showPayload = false"
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm"
                                            x-cloak
                                        >
                                            <div 
                                                @click.stop
                                                class="bg-white rounded-2xl shadow-2xl border border-[#1d1d1f]/10 max-w-lg w-full mx-4 overflow-hidden"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                            >
                                                <div class="flex items-center justify-between px-5 py-4 border-b border-[#1d1d1f]/[0.06]">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                                                        <h3 class="text-[15px] font-semibold text-[#1d1d1f]">Event Payload</h3>
                                                    </div>
                                                    <button @click="showPayload = false" class="p-1 rounded-lg hover:bg-[#f5f5f7] transition-colors">
                                                        <svg class="w-5 h-5 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </div>
                                                <div class="p-5">
                                                    <div class="space-y-2">
                                                        @foreach($log->payload as $key => $value)
                                                            <div class="flex items-start gap-3 py-1.5">
                                                                <span class="text-[12px] font-mono font-medium text-[#86868b] min-w-[140px] shrink-0">{{ $key }}</span>
                                                                <span class="text-[13px] text-[#1d1d1f] break-all">{{ is_array($value) ? json_encode($value) : ($value ?? '—') }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="px-5 py-3 bg-[#f5f5f7]/50 border-t border-[#1d1d1f]/[0.04]">
                                                    <div class="flex items-center gap-4 text-[11px] text-[#86868b]">
                                                        @if($log->ip_address)
                                                            <span class="flex items-center gap-1">
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" /></svg>
                                                                IP: {{ $log->ip_address }}
                                                            </span>
                                                        @endif
                                                        @if($log->user_agent)
                                                            <span class="truncate max-w-[280px]" title="{{ $log->user_agent }}">
                                                                UA: {{ Str::limit($log->user_agent, 50) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-[12px] text-[#86868b]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-[#1d1d1f]/[0.06]">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
