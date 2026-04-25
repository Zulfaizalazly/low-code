<div class="space-y-6 fade-in-up flex-1 flex flex-col">
    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[12px] text-[14px] font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">Branches</h1>
            <p class="text-[15px] text-[#86868b] mt-1">Manage all branches across the organization</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#1d1d1f] text-white text-[14px] font-semibold rounded-[12px] hover:bg-[#333336] transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Create Branch
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by code or name..."
                        class="w-full pl-10 pr-4 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] placeholder-[#86868b] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
                    />
                </div>
            </div>

            <!-- Region Filter -->
            <select
                wire:model.live="filterRegion"
                class="px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
            >
                <option value="">All Regions</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                @endforeach
            </select>

            <!-- State Filter -->
            <select
                wire:model.live="filterState"
                class="px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
            >
                <option value="">All States</option>
                @foreach($states as $state)
                    <option value="{{ $state }}">{{ $state }}</option>
                @endforeach
            </select>

            <!-- Type Filter -->
            <select
                wire:model.live="filterType"
                class="px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
            >
                <option value="">All Types</option>
                <option value="hq">HQ</option>
                <option value="branch">Branch</option>
                <option value="mini_branch">Mini Branch</option>
            </select>

            <!-- Status Filter -->
            <select
                wire:model.live="filterStatus"
                class="px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
            >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Branches Table -->
    <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] overflow-hidden flex-1">
        @if($branches->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-[#f5f5f7] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-[17px] font-semibold text-[#1d1d1f] mb-1">No branches found</h3>
                <p class="text-[14px] text-[#86868b]">Try adjusting your filters or create a new branch.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-black/[0.04]">
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Code</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Name</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Type</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Region</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">City/State</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Manager</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Staff</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($branches as $branch)
                            <tr class="hover:bg-black/[0.01] transition-colors">
                                <td class="px-6 py-3.5">
                                    <span class="text-[13px] font-mono font-medium text-[#515154]">{{ $branch->code }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] font-medium text-[#1d1d1f]">{{ $branch->name }}</span>
                                </td>
                                <td class="px-6 py-3.5">
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] text-[#515154]">{{ $branch->region?->name ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] text-[#515154]">
                                        @if($branch->city && $branch->state)
                                            {{ $branch->city }}, {{ $branch->state }}
                                        @elseif($branch->city)
                                            {{ $branch->city }}
                                        @elseif($branch->state)
                                            {{ $branch->state }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] text-[#515154]">{{ $branch->manager?->name ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] font-medium text-[#1d1d1f]">{{ $branch->active_staff_assignments_count }}</span>
                                </td>
                                <td class="px-6 py-3.5">
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
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.branches.show', $branch) }}" class="inline-flex items-center px-3 py-1.5 text-[12px] font-semibold text-blue-600 bg-blue-50 rounded-[8px] hover:bg-blue-100 transition-colors">
                                            View
                                        </a>
                                        <button wire:click="edit({{ $branch->id }})" class="inline-flex items-center px-3 py-1.5 text-[12px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[8px] hover:bg-black/[0.06] transition-colors">
                                            Edit
                                        </button>
                                        <button wire:click="toggleStatus({{ $branch->id }})" class="inline-flex items-center px-3 py-1.5 text-[12px] font-semibold {{ $branch->is_active ? 'text-amber-600 bg-amber-50 hover:bg-amber-100' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' }} rounded-[8px] transition-colors">
                                            {{ $branch->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-black/[0.04]">
                {{ $branches->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Branch Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/40 transition-opacity" wire:click="closeModal"></div>

                <!-- Modal Panel -->
                <div class="relative bg-white rounded-[20px] shadow-xl w-full max-w-2xl mx-auto z-10 overflow-hidden max-h-[90vh] flex flex-col">
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-black/[0.04]">
                        <h2 class="text-[20px] font-bold text-[#1d1d1f]">
                            {{ $editingBranchId ? 'Edit Branch' : 'Create Branch' }}
                        </h2>
                        <p class="text-[14px] text-[#86868b] mt-0.5">
                            {{ $editingBranchId ? 'Update the branch details below.' : 'Fill in the details to create a new branch.' }}
                        </p>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit="save" class="flex flex-col flex-1 min-h-0">
                        <div class="px-6 py-5 overflow-y-auto space-y-5 flex-1">
                            <!-- Code & Name -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="code" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Branch Code</label>
                                    <input type="text" id="code" wire:model="code" maxlength="20" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="e.g. BR001" />
                                    @error('code') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="name" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Branch Name</label>
                                    <input type="text" id="name" wire:model="name" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="Branch name" />
                                    @error('name') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Type & Region -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="type" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Type</label>
                                    <select id="type" wire:model="type" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                        <option value="branch">Branch</option>
                                        <option value="hq">HQ</option>
                                        <option value="mini_branch">Mini Branch</option>
                                    </select>
                                    @error('type') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="region_id" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Region</label>
                                    <select id="region_id" wire:model="region_id" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                        <option value="">— None —</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('region_id') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Address -->
                            <div>
                                <label for="address" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Address</label>
                                <textarea id="address" wire:model="address" rows="2" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 resize-none" placeholder="Street address"></textarea>
                                @error('address') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- City, State, Postcode -->
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label for="city" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">City</label>
                                    <input type="text" id="city" wire:model="city" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="City" />
                                    @error('city') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="state" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">State</label>
                                    <input type="text" id="state" wire:model="state" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="State" />
                                    @error('state') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="postcode" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Postcode</label>
                                    <input type="text" id="postcode" wire:model="postcode" maxlength="10" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="Postcode" />
                                    @error('postcode') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Phone & Email -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="phone" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Phone</label>
                                    <input type="text" id="phone" wire:model="phone" maxlength="20" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="Phone number" />
                                    @error('phone') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Email</label>
                                    <input type="email" id="email" wire:model="email" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="branch@example.com" />
                                    @error('email') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Manager -->
                            <div>
                                <label for="manager_id" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Manager</label>
                                <select id="manager_id" wire:model="manager_id" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                    <option value="">— No Manager —</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->email }})</option>
                                    @endforeach
                                </select>
                                @error('manager_id') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer" />
                                    <div class="w-9 h-5 bg-[#d1d1d6] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                                <span class="text-[14px] font-medium text-[#1d1d1f]">Active</span>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="shrink-0 px-6 py-4 border-t border-black/[0.04] flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-[14px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[12px] hover:bg-black/[0.06] transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-[14px] font-semibold text-white bg-[#1d1d1f] rounded-[12px] hover:bg-[#333336] transition-colors shadow-sm">
                                {{ $editingBranchId ? 'Update Branch' : 'Create Branch' }}
                            </button>
                        </div>
                    </form>
                </div>
        </div>
    @endif
</div>
