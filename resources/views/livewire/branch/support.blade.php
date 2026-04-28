<div class="max-w-6xl mx-auto">
    <header class="mb-10 fade-in-up">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-[32px] font-bold tracking-[-0.02em] text-[#1d1d1f] leading-none">IT Support</h1>
                <p class="text-[15px] font-medium text-[#86868b]">Submit tickets and communicate with the IT department.</p>
            </div>
            <button
                wire:click="openNewTicket"
                class="px-5 py-2.5 bg-[#1d1d1f] hover:bg-[#333336] text-white text-[14px] font-semibold rounded-[14px] transition-all shadow-[0_8px_20px_rgba(0,0,0,0.08)] hover:shadow-[0_12px_24px_rgba(0,0,0,0.12)] hover:-translate-y-0.5 active:scale-[0.98] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                New Ticket
            </button>
        </div>
    </header>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-8 px-6 py-4 bg-emerald-50 border border-emerald-100 rounded-[20px] flex items-center gap-4 shadow-sm fade-in-up">
            <div class="w-8 h-8 rounded-full bg-emerald-100/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
            </div>
            <p class="text-[14px] text-emerald-800 font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- ─── Tickets List ─── --}}
        <div class="lg:col-span-2">
            {{-- Segmented Control Filter Tabs --}}
            <div class="flex items-center p-1 mb-6 bg-gray-100/80 backdrop-blur-md rounded-[16px] w-fit border border-black/[0.03]">
                <button wire:click="setFilter('open')" class="px-5 py-2 text-[13px] font-bold rounded-[12px] transition-all duration-300 {{ $filter === 'open' ? 'bg-white text-[#1d1d1f] shadow-[0_2px_8px_rgba(0,0,0,0.06)]' : 'text-[#86868b] hover:text-[#1d1d1f]' }}">
                    Open
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-md text-[10px] {{ $filter === 'open' ? 'bg-black/5' : 'bg-black/[0.03]' }}">{{ $openCount }}</span>
                </button>
                <button wire:click="setFilter('resolved')" class="px-5 py-2 text-[13px] font-bold rounded-[12px] transition-all duration-300 {{ $filter === 'resolved' ? 'bg-white text-[#1d1d1f] shadow-[0_2px_8px_rgba(0,0,0,0.06)]' : 'text-[#86868b] hover:text-[#1d1d1f]' }}">
                    Resolved
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-md text-[10px] {{ $filter === 'resolved' ? 'bg-black/5' : 'bg-black/[0.03]' }}">{{ $resolvedCount }}</span>
                </button>
                <button wire:click="setFilter('all')" class="px-5 py-2 text-[13px] font-bold rounded-[12px] transition-all duration-300 {{ $filter === 'all' ? 'bg-white text-[#1d1d1f] shadow-[0_2px_8px_rgba(0,0,0,0.06)]' : 'text-[#86868b] hover:text-[#1d1d1f]' }}">
                    All
                </button>
            </div>

            <div class="space-y-4">
                @forelse($tickets as $ticket)
                    <div class="group bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.02)] rounded-[24px] p-6 hover:shadow-[0_20px_40px_rgb(0,0,0,0.05)] hover:-translate-y-0.5 transition-all duration-500 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-b from-white/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                        <div class="flex items-start justify-between mb-4 relative">
                            <div class="flex-1 pr-4">
                                <div class="flex items-center gap-3 mb-1.5">
                                    <h4 class="text-[16px] font-bold text-[#1d1d1f] tracking-tight">{{ $ticket->title }}</h4>
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-{{ $ticket->priority_color }}-50/80 text-{{ $ticket->priority_color }}-700 border border-{{ $ticket->priority_color }}-200/50">
                                        {{ $ticket->priority }}
                                    </span>
                                </div>
                                <p class="text-[14px] text-[#515154] leading-relaxed line-clamp-2">{{ $ticket->description }}</p>
                            </div>
                            <span class="px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-full bg-{{ $ticket->status_color }}-50 text-{{ $ticket->status_color }}-700 border border-{{ $ticket->status_color }}-200/50 shrink-0 ml-3 shadow-sm">
                                {{ str_replace('_', ' ', $ticket->status) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-5 text-[12px] font-medium text-[#86868b] relative">
                            <span class="flex items-center gap-1.5 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-100">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                {{ ucfirst(str_replace('_', ' ', $ticket->category)) }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $ticket->created_at->diffForHumans() }}
                            </span>
                            @if($ticket->responded_at)
                                <span class="flex items-center gap-1.5 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    Replied {{ $ticket->responded_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>

                        @if($ticket->response_note)
                            <div class="mt-5 p-4 bg-gradient-to-br from-blue-50/80 to-indigo-50/40 border border-blue-100/60 rounded-[16px] relative">
                                <p class="text-[11px] font-bold text-blue-600 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    IT Response
                                </p>
                                <p class="text-[14px] text-[#1d1d1f] leading-relaxed">{{ $ticket->response_note }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white border border-black/[0.04] rounded-[24px] p-16 text-center flex flex-col items-center shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-5 border border-gray-100">
                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <p class="text-[16px] font-bold text-[#1d1d1f] tracking-tight mb-1">No {{ $filter === 'open' ? 'open' : ($filter === 'resolved' ? 'resolved' : '') }} tickets</p>
                        <p class="text-[14px] font-medium text-[#86868b] max-w-sm">Everything seems to be running smoothly. Submit a new ticket if you need IT assistance.</p>
                        <button wire:click="openNewTicket" class="mt-6 px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-[#1d1d1f] text-[13px] font-bold rounded-xl transition-all">
                            Create Ticket
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ─── Sidebar Area ─── --}}
        <div class="space-y-6">
            {{-- IT Contact Info --}}
            <div class="bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.03)] rounded-[24px] p-6">
                <h3 class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-5">IT Department Info</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-3 rounded-[16px] hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                        <div class="w-12 h-12 rounded-[14px] bg-gradient-to-tr from-blue-50 to-indigo-50 flex items-center justify-center shrink-0 border border-blue-100/50 shadow-sm">
                            <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-[#86868b] uppercase tracking-wide mb-0.5">Email Support</p>
                            <p class="text-[14px] text-[#1d1d1f] font-semibold truncate">{{ $itSupport['email'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-3 rounded-[16px] hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                        <div class="w-12 h-12 rounded-[14px] bg-gradient-to-tr from-emerald-50 to-teal-50 flex items-center justify-center shrink-0 border border-emerald-100/50 shadow-sm">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-[#86868b] uppercase tracking-wide mb-0.5">Hotline</p>
                            <p class="text-[14px] text-[#1d1d1f] font-semibold truncate">{{ $itSupport['phone'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-3 rounded-[16px] hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                        <div class="w-12 h-12 rounded-[14px] bg-gradient-to-tr from-green-50 to-emerald-50 flex items-center justify-center shrink-0 border border-green-100/50 shadow-sm">
                            <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-[#86868b] uppercase tracking-wide mb-0.5">Support Hours</p>
                            <p class="text-[14px] text-[#1d1d1f] font-semibold truncate">{{ $itSupport['hours'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="bg-white border border-black/[0.04] shadow-[0_8px_30px_rgb(0,0,0,0.03)] rounded-[24px] p-6 relative overflow-hidden group">
                <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
                <h3 class="text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-5 relative">Ticket Summary</h3>
                <div class="space-y-4 relative">
                    <div class="flex items-center justify-between">
                        <span class="text-[14px] font-medium text-[#515154]">Open Tickets</span>
                        <span class="text-[16px] font-bold text-green-600 bg-green-50 px-2.5 py-0.5 rounded-lg border border-green-100">{{ $openCount }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[14px] font-medium text-[#515154]">Resolved</span>
                        <span class="text-[16px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-100">{{ $resolvedCount }}</span>
                    </div>
                    <div class="w-full bg-gray-100/80 rounded-full h-2 overflow-hidden mt-2 border border-black/[0.03]">
                        @php $resolvedPercent = ($openCount + $resolvedCount) > 0 ? ($resolvedCount / ($openCount + $resolvedCount)) * 100 : 100; @endphp
                        <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full transition-all duration-1000 ease-out shadow-sm" style="width: {{ $resolvedPercent }}%"></div>
                    </div>
                    <p class="text-[12px] font-bold text-[#86868b] text-right">{{ round($resolvedPercent) }}% Resolution Rate</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── New Ticket Modal ─── --}}
    @if($showNewTicket)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:keydown.escape="$set('showNewTicket', false)">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm transition-opacity" wire:click="$set('showNewTicket', false)"></div>

        <div class="relative bg-white/95 backdrop-blur-2xl rounded-[32px] shadow-[0_40px_100px_rgba(0,0,0,0.15)] w-full max-w-lg overflow-hidden border border-white/60 fade-in-up">
            <div class="px-8 pt-8 pb-5 border-b border-black/[0.04]">
                <h2 class="text-[24px] font-bold text-[#1d1d1f] tracking-tight leading-none mb-1.5">Submit Ticket</h2>
                <p class="text-[14px] font-medium text-[#86868b]">Describe your issue and the IT team will be notified.</p>
            </div>

            <div class="px-8 py-6 space-y-5 bg-[#fcfcfc]/50">
                <div>
                    <label class="block text-[12px] font-bold text-[#515154] uppercase tracking-wider mb-2">Title</label>
                    <input
                        type="text"
                        wire:model="ticketTitle"
                        placeholder="Brief description of the issue"
                        class="w-full px-4 py-3.5 rounded-[16px] border border-black/[0.06] bg-white text-[15px] font-medium text-[#1d1d1f] placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm"
                        autofocus
                    >
                    @error('ticketTitle')<p class="text-[12px] font-medium text-rose-500 mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-[12px] font-bold text-[#515154] uppercase tracking-wider mb-2">Description</label>
                    <textarea
                        wire:model="ticketDescription"
                        rows="4"
                        placeholder="Provide details about the issue, when it occurred, and what steps to reproduce..."
                        class="w-full px-4 py-3.5 rounded-[16px] border border-black/[0.06] bg-white text-[15px] font-medium text-[#1d1d1f] placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all resize-none shadow-sm"
                    ></textarea>
                    @error('ticketDescription')<p class="text-[12px] font-medium text-rose-500 mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[12px] font-bold text-[#515154] uppercase tracking-wider mb-2">Category</label>
                        <div class="relative">
                            <select wire:model="ticketCategory" class="w-full px-4 py-3.5 rounded-[16px] border border-black/[0.06] bg-white text-[15px] font-medium text-[#1d1d1f] focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all appearance-none shadow-sm cursor-pointer">
                                <option value="issue">General Issue</option>
                                <option value="bug">Bug Report</option>
                                <option value="feature_request">Feature Request</option>
                            </select>
                            <svg class="w-4 h-4 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-[#515154] uppercase tracking-wider mb-2">Priority</label>
                        <div class="relative">
                            <select wire:model="ticketPriority" class="w-full px-4 py-3.5 rounded-[16px] border border-black/[0.06] bg-white text-[15px] font-medium text-[#1d1d1f] focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all appearance-none shadow-sm cursor-pointer">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                            <svg class="w-4 h-4 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 bg-white border-t border-black/[0.04] flex justify-end gap-3">
                <button
                    wire:click="$set('showNewTicket', false)"
                    class="px-5 py-2.5 rounded-[14px] border border-black/[0.06] bg-white text-[14px] font-bold text-[#515154] hover:text-[#1d1d1f] hover:bg-gray-50 transition-all">
                    Cancel
                </button>
                <button
                    wire:click="submitTicket"
                    wire:loading.attr="disabled"
                    class="px-6 py-2.5 bg-[#1d1d1f] hover:bg-[#333336] text-white text-[14px] font-bold rounded-[14px] transition-all shadow-[0_4px_12px_rgba(0,0,0,0.1)] active:scale-[0.98] flex items-center gap-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="submitTicket">Submit Ticket</span>
                    <span wire:loading wire:target="submitTicket" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Submitting...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
