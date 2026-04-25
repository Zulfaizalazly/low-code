<div class="space-y-6 fade-in-up">
    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[12px] text-[14px] font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Back Link & Page Header -->
    <div>
        <a href="{{ route('admin.branches') }}" class="inline-flex items-center gap-1.5 text-[14px] font-medium text-[#86868b] hover:text-[#1d1d1f] transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to Branches
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">{{ $branch->name }}</h1>
            @php
                $badgeClass = match($branch->type) {
                    'hq' => 'bg-blue-100 text-blue-700',
                    'branch' => 'bg-green-100 text-green-700',
                    'mini_branch' => 'bg-amber-100 text-amber-700',
                    default => 'bg-gray-100 text-gray-700',
                };
                $badgeLabel = match($branch->type) {
                    'hq' => 'HQ',
                    'branch' => 'Branch',
                    'mini_branch' => 'Mini Branch',
                    default => ucfirst($branch->type),
                };
            @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[12px] font-semibold {{ $badgeClass }}">
                {{ $badgeLabel }}
            </span>
            @if($branch->is_active)
                <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-emerald-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Active
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#86868b]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#d1d1d6]"></span>
                    Inactive
                </span>
            @endif
        </div>
        <p class="text-[15px] text-[#86868b] mt-1">{{ $branch->code }} · {{ $branch->region?->name ?? 'No Region' }}</p>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
        <div class="flex border-b border-black/[0.04]">
            <button wire:click="setTab('overview')" class="px-6 py-3 text-[14px] font-semibold border-b-2 transition-colors {{ $activeTab === 'overview' ? 'text-[#1d1d1f] border-[#1d1d1f]' : 'text-[#86868b] border-transparent hover:text-[#515154]' }}">
                Overview
            </button>
            <button wire:click="setTab('staff')" class="px-6 py-3 text-[14px] font-semibold border-b-2 transition-colors {{ $activeTab === 'staff' ? 'text-[#1d1d1f] border-[#1d1d1f]' : 'text-[#86868b] border-transparent hover:text-[#515154]' }}">
                Staff
            </button>
            <button wire:click="setTab('settings')" class="px-6 py-3 text-[14px] font-semibold border-b-2 transition-colors {{ $activeTab === 'settings' ? 'text-[#1d1d1f] border-[#1d1d1f]' : 'text-[#86868b] border-transparent hover:text-[#515154]' }}">
                Settings
            </button>
        </div>

        <div class="p-6">
            {{-- ==================== OVERVIEW TAB ==================== --}}
            @if($activeTab === 'overview')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Branch Information -->
                    <div class="bg-[#f5f5f7] rounded-[14px] p-5 space-y-4">
                        <h3 class="text-[15px] font-bold text-[#1d1d1f]">Branch Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Code</span>
                                <span class="text-[13px] font-medium text-[#1d1d1f] font-mono">{{ $branch->code }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Name</span>
                                <span class="text-[13px] font-medium text-[#1d1d1f]">{{ $branch->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Type</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Region</span>
                                <span class="text-[13px] font-medium text-[#1d1d1f]">{{ $branch->region?->name ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Entity</span>
                                <span class="text-[13px] font-medium text-[#1d1d1f]">{{ $branch->entity?->name ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Opened</span>
                                <span class="text-[13px] font-medium text-[#1d1d1f]">{{ $branch->opened_at?->format('d M Y') ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Status</span>
                                @if($branch->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-emerald-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#86868b]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#d1d1d6]"></span> Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div class="bg-[#f5f5f7] rounded-[14px] p-5 space-y-4">
                        <h3 class="text-[15px] font-bold text-[#1d1d1f]">Contact Details</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Address</span>
                                <span class="text-[13px] font-medium text-[#1d1d1f] text-right max-w-[60%]">{{ $branch->full_address ?: '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Phone</span>
                                <span class="text-[13px] font-medium text-[#1d1d1f]">{{ $branch->phone ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[13px] text-[#86868b]">Email</span>
                                <span class="text-[13px] font-medium text-[#1d1d1f]">{{ $branch->email ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Hours -->
                    <div class="bg-[#f5f5f7] rounded-[14px] p-5 space-y-4">
                        <h3 class="text-[15px] font-bold text-[#1d1d1f]">Operating Hours</h3>
                        @if($branch->opening_hours && count($branch->opening_hours) > 0)
                            <div class="space-y-2">
                                @foreach($branch->opening_hours as $day => $hours)
                                    <div class="flex justify-between">
                                        <span class="text-[13px] text-[#86868b] capitalize">{{ $day }}</span>
                                        <span class="text-[13px] font-medium text-[#1d1d1f]">{{ is_array($hours) ? implode(' - ', $hours) : $hours }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[13px] text-[#86868b]">No operating hours configured.</p>
                        @endif
                    </div>

                    <!-- Manager Details -->
                    <div class="bg-[#f5f5f7] rounded-[14px] p-5 space-y-4">
                        <h3 class="text-[15px] font-bold text-[#1d1d1f]">Manager</h3>
                        @if($branch->manager)
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-violet-400 to-purple-500 flex items-center justify-center shrink-0">
                                    <span class="text-[13px] font-bold text-white">{{ substr($branch->manager->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-[14px] font-semibold text-[#1d1d1f]">{{ $branch->manager->name }}</p>
                                    <p class="text-[12px] text-[#86868b]">{{ $branch->manager->email }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-[13px] text-[#86868b]">No manager assigned.</p>
                        @endif
                    </div>
                </div>

            {{-- ==================== STAFF TAB ==================== --}}
            @elseif($activeTab === 'staff')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[15px] font-bold text-[#1d1d1f]">Active Staff ({{ $branch->activeStaffAssignments->count() }})</h3>
                        <a href="{{ route('admin.staff') }}" class="inline-flex items-center gap-2 px-4 py-2 text-[13px] font-semibold text-[#1d1d1f] bg-[#f5f5f7] rounded-[10px] hover:bg-black/[0.06] transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Assign / Transfer Staff
                        </a>
                    </div>

                    @if($branch->activeStaffAssignments->isEmpty())
                        <div class="p-8 text-center">
                            <div class="w-14 h-14 rounded-full bg-[#f5f5f7] flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h4 class="text-[15px] font-semibold text-[#1d1d1f] mb-1">No staff assigned</h4>
                            <p class="text-[13px] text-[#86868b]">Assign staff to this branch from the Staff page.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-black/[0.04]">
                                        <th class="text-left px-4 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Name</th>
                                        <th class="text-left px-4 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Position</th>
                                        <th class="text-left px-4 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Employment Type</th>
                                        <th class="text-left px-4 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Start Date</th>
                                        <th class="text-left px-4 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Primary</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04]">
                                    @foreach($branch->activeStaffAssignments as $assignment)
                                        <tr class="hover:bg-black/[0.01] transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-violet-400 to-purple-500 flex items-center justify-center shrink-0">
                                                        <span class="text-[11px] font-bold text-white">{{ substr($assignment->user?->name ?? '?', 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <p class="text-[14px] font-medium text-[#1d1d1f]">{{ $assignment->user?->name ?? '—' }}</p>
                                                        <p class="text-[12px] text-[#86868b]">{{ $assignment->user?->email ?? '' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-[14px] text-[#515154]">{{ $assignment->position ?? '—' }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @php
                                                    $empBadge = match($assignment->employment_type) {
                                                        'permanent' => 'bg-blue-100 text-blue-700',
                                                        'contract' => 'bg-amber-100 text-amber-700',
                                                        'temporary' => 'bg-purple-100 text-purple-700',
                                                        default => 'bg-gray-100 text-gray-700',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $empBadge }}">
                                                    {{ ucfirst($assignment->employment_type ?? '—') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-[14px] text-[#515154]">{{ $assignment->started_at?->format('d M Y') ?? '—' }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($assignment->is_primary)
                                                    <span class="inline-flex items-center gap-1 text-[12px] font-semibold text-emerald-600">
                                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                        Yes
                                                    </span>
                                                @else
                                                    <span class="text-[12px] text-[#86868b]">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            {{-- ==================== SETTINGS TAB ==================== --}}
            @elseif($activeTab === 'settings')
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-[15px] font-bold text-[#1d1d1f]">Branch Settings</h3>
                            <p class="text-[13px] text-[#86868b] mt-0.5">Configure key-value settings for this branch.</p>
                        </div>
                        <button wire:click="saveSettings" class="inline-flex items-center gap-2 px-4 py-2 text-[13px] font-semibold text-white bg-[#1d1d1f] rounded-[10px] hover:bg-[#333336] transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Save Settings
                        </button>
                    </div>

                    <!-- Existing Settings -->
                    @if(count($settings) > 0)
                        <div class="space-y-2">
                            @foreach($settings as $key => $value)
                                <div class="flex items-center gap-3 bg-[#f5f5f7] rounded-[10px] p-3">
                                    <div class="flex-1 min-w-0">
                                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1">{{ $key }}</label>
                                        <input
                                            type="text"
                                            wire:model="settings.{{ $key }}"
                                            class="w-full px-3 py-1.5 text-[14px] bg-white border border-black/[0.04] rounded-[8px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
                                        />
                                    </div>
                                    <button wire:click="removeSetting('{{ $key }}')" class="shrink-0 p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-[8px] transition-colors" title="Remove setting">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center bg-[#f5f5f7] rounded-[14px]">
                            <p class="text-[14px] text-[#86868b]">No settings configured yet. Add your first setting below.</p>
                        </div>
                    @endif

                    <!-- Add New Setting -->
                    <div class="bg-[#f5f5f7] rounded-[14px] p-5">
                        <h4 class="text-[14px] font-semibold text-[#1d1d1f] mb-3">Add New Setting</h4>
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                <label class="block text-[12px] font-semibold text-[#86868b] mb-1">Key</label>
                                <input
                                    type="text"
                                    wire:model="newSettingKey"
                                    placeholder="e.g. max_transactions"
                                    class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.04] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
                                />
                                @error('newSettingKey') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex-1">
                                <label class="block text-[12px] font-semibold text-[#86868b] mb-1">Value</label>
                                <input
                                    type="text"
                                    wire:model="newSettingValue"
                                    placeholder="e.g. 100"
                                    class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.04] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
                                />
                                @error('newSettingValue') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <button wire:click="addSetting" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 text-[13px] font-semibold text-[#1d1d1f] bg-white border border-black/[0.04] rounded-[10px] hover:bg-black/[0.02] transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                Add
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
