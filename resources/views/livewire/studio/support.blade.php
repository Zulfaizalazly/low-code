<div>
    {{-- ─── Header ─── --}}
    <header class="mb-10">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-sm flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <div>
                <h1 class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">IT Support Management</h1>
                <p class="text-[14px] text-[#86868b] font-medium">Manage and respond to support tickets from all branches.</p>
            </div>
        </div>
    </header>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-6 px-5 py-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <p class="text-[14px] text-emerald-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @error('status')
        <div class="mb-6 px-5 py-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-[14px] text-rose-700 font-medium">{{ $message }}</p>
        </div>
    @enderror

    {{-- ─── Analytics Cards ─── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        {{-- Open Tickets --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-green-600">Active</span>
            </div>
            <p class="text-[32px] font-bold tracking-tight text-[#1d1d1f] leading-none">{{ $openCount }}</p>
            <p class="text-[13px] text-[#86868b] mt-1">Open Tickets</p>
        </div>

        {{-- Resolved Tickets --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">Resolved</span>
            </div>
            <p class="text-[32px] font-bold tracking-tight text-[#1d1d1f] leading-none">{{ $resolvedCount }}</p>
            <p class="text-[13px] text-[#86868b] mt-1">Resolved Tickets</p>
        </div>

        {{-- Avg Resolution Time --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-blue-600">Speed</span>
            </div>
            <p class="text-[32px] font-bold tracking-tight text-[#1d1d1f] leading-none">{{ $avgResolutionTime }}<span class="text-[16px] font-medium text-[#86868b] ml-1">hrs</span></p>
            <p class="text-[13px] text-[#86868b] mt-1">Avg Resolution Time</p>
        </div>
    </div>

    {{-- ─── Filter Bar ─── --}}
    <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search tickets..."
                    class="w-full pl-10 pr-4 py-2.5 text-[14px] bg-[#f5f5f7] border-0 rounded-xl text-[#1d1d1f] placeholder-[#86868b] focus:ring-2 focus:ring-blue-500/30 focus:bg-white transition-all"
                >
            </div>

            {{-- Status Filter --}}
            <select wire:model.live="statusFilter" class="px-4 py-2.5 text-[14px] bg-[#f5f5f7] border-0 rounded-xl text-[#1d1d1f] focus:ring-2 focus:ring-blue-500/30 focus:bg-white transition-all cursor-pointer appearance-none min-w-[140px]">
                <option value="">All Status</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>

            {{-- Priority Filter --}}
            <select wire:model.live="priorityFilter" class="px-4 py-2.5 text-[14px] bg-[#f5f5f7] border-0 rounded-xl text-[#1d1d1f] focus:ring-2 focus:ring-blue-500/30 focus:bg-white transition-all cursor-pointer appearance-none min-w-[140px]">
                <option value="">All Priority</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
            </select>

            {{-- Category Filter --}}
            <select wire:model.live="categoryFilter" class="px-4 py-2.5 text-[14px] bg-[#f5f5f7] border-0 rounded-xl text-[#1d1d1f] focus:ring-2 focus:ring-blue-500/30 focus:bg-white transition-all cursor-pointer appearance-none min-w-[150px]">
                <option value="">All Categories</option>
                <option value="bug">Bug</option>
                <option value="feature_request">Feature Request</option>
                <option value="issue">Issue</option>
            </select>

            {{-- Branch Filter --}}
            <select wire:model.live="branchFilter" class="px-4 py-2.5 text-[14px] bg-[#f5f5f7] border-0 rounded-xl text-[#1d1d1f] focus:ring-2 focus:ring-blue-500/30 focus:bg-white transition-all cursor-pointer appearance-none min-w-[150px]">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>

            {{-- Reset --}}
            <button wire:click="resetFilters" class="px-4 py-2.5 text-[13px] font-medium text-[#86868b] hover:text-[#1d1d1f] bg-[#f5f5f7] hover:bg-[#e5e5ea] rounded-xl transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                Reset
            </button>
        </div>
    </div>

    {{-- ─── Ticket List Table ─── --}}
    <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] shadow-sm overflow-hidden mb-8">
        @if($tickets->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-[#f5f5f7] flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-[#86868b]/60" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h3 class="text-[16px] font-semibold text-[#1d1d1f] mb-1">No Tickets Found</h3>
                <p class="text-[14px] text-[#86868b] max-w-sm">No support tickets match your current filters. Try adjusting your search criteria.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#1d1d1f]/[0.06]">
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Title</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Submitter</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Branch</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Category</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Priority</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Status</th>
                            <th class="text-left px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1d1d1f]/[0.04]">
                        @foreach($tickets as $ticket)
                            <tr
                                wire:click="selectTicket({{ $ticket->id }})"
                                wire:key="ticket-{{ $ticket->id }}"
                                class="hover:bg-[#f5f5f7]/50 transition-colors cursor-pointer group"
                            >
                                <td class="px-6 py-4">
                                    <p class="text-[14px] font-medium text-[#1d1d1f] group-hover:text-blue-600 transition-colors truncate max-w-[240px]">{{ $ticket->title }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#e5e5ea] to-[#d1d1d6] flex items-center justify-center text-[11px] font-bold text-[#515154] shrink-0">
                                            {{ strtoupper(substr($ticket->user?->name ?? '?', 0, 1)) }}
                                        </div>
                                        <span class="text-[13px] text-[#515154]">{{ $ticket->user?->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-[13px] text-[#515154]">{{ $ticket->branch?->name ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[11px] font-medium rounded-lg bg-[#f5f5f7] text-[#515154] border border-[#1d1d1f]/[0.06]">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->category)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-{{ $ticket->priority_color }}-50 text-{{ $ticket->priority_color }}-700 border border-{{ $ticket->priority_color }}-200/50">
                                        {{ $ticket->priority }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-{{ $ticket->status_color }}-50 text-{{ $ticket->status_color }}-700 border border-{{ $ticket->status_color }}-200/50">
                                        {{ str_replace('_', ' ', $ticket->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-[13px] text-[#515154]">{{ $ticket->created_at->format('d M Y') }}</div>
                                    <div class="text-[11px] text-[#86868b]">{{ $ticket->created_at->format('H:i') }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-[#1d1d1f]/[0.06]">
                    {{ $tickets->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ─── Breakdowns ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        {{-- Priority Breakdown --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm">
            <h3 class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-4">Priority Breakdown</h3>
            <div class="space-y-3">
                @php
                    $priorityColors = ['critical' => 'rose', 'high' => 'green', 'medium' => 'emerald', 'low' => 'blue'];
                    $priorityOrder = ['critical', 'high', 'medium', 'low'];
                @endphp
                @foreach($priorityOrder as $priority)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-{{ $priorityColors[$priority] }}-500"></div>
                            <span class="text-[13px] font-medium text-[#515154] capitalize">{{ $priority }}</span>
                        </div>
                        <span class="text-[14px] font-bold text-[#1d1d1f] bg-{{ $priorityColors[$priority] }}-50 px-2 py-0.5 rounded-lg border border-{{ $priorityColors[$priority] }}-100">
                            {{ $priorityBreakdown[$priority] ?? 0 }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Category Breakdown --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm">
            <h3 class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-4">Category Breakdown</h3>
            <div class="space-y-3">
                @php
                    $categoryColors = ['bug' => 'rose', 'feature_request' => 'purple', 'issue' => 'green'];
                    $categoryLabels = ['bug' => 'Bug', 'feature_request' => 'Feature Request', 'issue' => 'Issue'];
                @endphp
                @foreach($categoryLabels as $catKey => $catLabel)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-{{ $categoryColors[$catKey] }}-500"></div>
                            <span class="text-[13px] font-medium text-[#515154]">{{ $catLabel }}</span>
                        </div>
                        <span class="text-[14px] font-bold text-[#1d1d1f] bg-{{ $categoryColors[$catKey] }}-50 px-2 py-0.5 rounded-lg border border-{{ $categoryColors[$catKey] }}-100">
                            {{ $categoryBreakdown[$catKey] ?? 0 }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Branch Breakdown --}}
        <div class="bg-white rounded-2xl border border-[#1d1d1f]/[0.06] p-5 shadow-sm">
            <h3 class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-4">Branch Breakdown</h3>
            <div class="space-y-3">
                @forelse($branchBreakdown as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span class="text-[13px] font-medium text-[#515154] truncate max-w-[160px]">{{ $item->branch?->name ?? 'Unassigned' }}</span>
                        </div>
                        <span class="text-[14px] font-bold text-[#1d1d1f] bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100">
                            {{ $item->count }}
                        </span>
                    </div>
                @empty
                    <p class="text-[13px] text-[#86868b]">No open tickets by branch.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ─── Ticket Detail Slide-over Panel ─── --}}
    @if($selectedTicket)
    <div class="fixed inset-0 z-50 flex justify-end" wire:keydown.escape="closeDetail">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm transition-opacity" wire:click="closeDetail"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-xl bg-white shadow-[0_32px_80px_rgba(0,0,0,0.15)] overflow-y-auto">
            {{-- Panel Header --}}
            <div class="sticky top-0 z-10 bg-white/95 backdrop-blur-md border-b border-[#1d1d1f]/[0.06] px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-{{ $selectedTicket->status_color }}-50 text-{{ $selectedTicket->status_color }}-700 border border-{{ $selectedTicket->status_color }}-200/50">
                        {{ str_replace('_', ' ', $selectedTicket->status) }}
                    </span>
                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-{{ $selectedTicket->priority_color }}-50 text-{{ $selectedTicket->priority_color }}-700 border border-{{ $selectedTicket->priority_color }}-200/50">
                        {{ $selectedTicket->priority }}
                    </span>
                </div>
                <button wire:click="closeDetail" class="p-2 rounded-xl hover:bg-[#f5f5f7] transition-colors">
                    <svg class="w-5 h-5 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="px-6 py-6 space-y-6">
                {{-- Title & Description --}}
                <div>
                    <h2 class="text-[20px] font-bold text-[#1d1d1f] tracking-tight mb-3">{{ $selectedTicket->title }}</h2>
                    <p class="text-[14px] text-[#515154] leading-relaxed whitespace-pre-wrap">{{ $selectedTicket->description }}</p>
                </div>

                {{-- Submitter Info --}}
                <div class="bg-[#f5f5f7] rounded-2xl p-4 space-y-3">
                    <h4 class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Submitter Information</h4>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#e5e5ea] to-[#d1d1d6] flex items-center justify-center text-[14px] font-bold text-[#515154] shrink-0">
                            {{ strtoupper(substr($selectedTicket->user?->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-[14px] font-semibold text-[#1d1d1f]">{{ $selectedTicket->user?->name ?? 'Unknown' }}</p>
                            <p class="text-[12px] text-[#86868b]">{{ $selectedTicket->user?->email ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <p class="text-[11px] font-medium text-[#86868b] uppercase tracking-wide mb-0.5">Branch</p>
                            <p class="text-[13px] font-medium text-[#1d1d1f]">{{ $selectedTicket->branch?->name ?? 'Unassigned' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-[#86868b] uppercase tracking-wide mb-0.5">Category</p>
                            <p class="text-[13px] font-medium text-[#1d1d1f]">{{ ucfirst(str_replace('_', ' ', $selectedTicket->category)) }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-[#86868b] uppercase tracking-wide mb-0.5">Created</p>
                            <p class="text-[13px] font-medium text-[#1d1d1f]">{{ $selectedTicket->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        @if($selectedTicket->resolved_at)
                        <div>
                            <p class="text-[11px] font-medium text-[#86868b] uppercase tracking-wide mb-0.5">Resolved</p>
                            <p class="text-[13px] font-medium text-[#1d1d1f]">{{ $selectedTicket->resolved_at->format('d M Y, H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Context JSON --}}
                @if($selectedTicket->context_json && count($selectedTicket->context_json) > 0)
                <div class="bg-[#f5f5f7] rounded-2xl p-4">
                    <h4 class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wider mb-3">Context Information</h4>
                    <div class="space-y-2">
                        @foreach($selectedTicket->context_json as $key => $value)
                            <div class="flex items-start gap-3 py-1">
                                <span class="text-[12px] font-mono font-medium text-[#86868b] min-w-[120px] shrink-0">{{ $key }}</span>
                                <span class="text-[13px] text-[#1d1d1f] break-all">{{ is_array($value) ? json_encode($value) : ($value ?? '—') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Existing Response --}}
                @if($selectedTicket->response_note)
                <div class="bg-blue-50/80 border border-blue-100/60 rounded-2xl p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        <h4 class="text-[11px] font-semibold text-blue-700 uppercase tracking-wider">IT Response</h4>
                    </div>
                    <p class="text-[14px] text-[#1d1d1f] leading-relaxed mb-3">{{ $selectedTicket->response_note }}</p>
                    <div class="flex items-center gap-2 text-[12px] text-blue-600">
                        <span class="font-medium">{{ $selectedTicket->responder?->name ?? 'Unknown' }}</span>
                        @if($selectedTicket->responded_at)
                            <span class="text-blue-400">·</span>
                            <span>{{ $selectedTicket->responded_at->format('d M Y, H:i') }}</span>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Response Form --}}
                <div class="border border-[#1d1d1f]/[0.06] rounded-2xl p-4">
                    <h4 class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wider mb-3">
                        {{ $selectedTicket->response_note ? 'Update Response' : 'Write Response' }}
                    </h4>
                    <textarea
                        wire:model="responseNote"
                        rows="4"
                        placeholder="Write your response to this ticket (min 5 characters)..."
                        class="w-full px-4 py-3 rounded-xl border border-[#1d1d1f]/[0.08] bg-[#fcfcfc] text-[14px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 transition-all resize-none"
                    ></textarea>
                    @error('responseNote')
                        <p class="text-[12px] text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                    <div class="flex justify-end mt-3">
                        <button
                            wire:click="submitResponse"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 bg-[#1d1d1f] hover:bg-[#434346] text-white text-[13px] font-semibold rounded-xl transition-all shadow-md shadow-black/10 flex items-center gap-2 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="submitResponse">Submit Response</span>
                            <span wire:loading wire:target="submitResponse" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Submitting...
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Status Transition Buttons --}}
                <div class="border border-[#1d1d1f]/[0.06] rounded-2xl p-4">
                    <h4 class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wider mb-3">Status Actions</h4>
                    <div class="flex flex-wrap gap-2">
                        @if($selectedTicket->status === 'open')
                            <button
                                wire:click="updateStatus('in_progress')"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-xl transition-all flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                Mark In Progress
                            </button>
                        @elseif($selectedTicket->status === 'in_progress')
                            <button
                                wire:click="updateStatus('resolved')"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[13px] font-semibold rounded-xl transition-all flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Mark Resolved
                            </button>
                        @elseif($selectedTicket->status === 'resolved')
                            <button
                                wire:click="updateStatus('closed')"
                                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-[13px] font-semibold rounded-xl transition-all flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Close Ticket
                            </button>
                        @elseif($selectedTicket->status === 'closed')
                            <p class="text-[13px] text-[#86868b] font-medium py-2">This ticket is closed. No further actions available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
