<div class="space-y-6 fade-in-up flex-1 flex flex-col">
    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[12px] text-[14px] font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-[12px] text-[14px] font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">Staff</h1>
            <p class="text-[15px] text-[#86868b] mt-1">Manage staff members, assignments, and transfers</p>
        </div>
        <button wire:click="createStaff" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#1d1d1f] text-white text-[14px] font-semibold rounded-[12px] hover:bg-[#333336] transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Create Staff
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, email, or employee number..." class="w-full pl-10 pr-4 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] placeholder-[#86868b] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                </div>
            </div>

            <!-- Branch Filter -->
            <select wire:model.live="filterBranch" class="px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>

            <!-- Department Filter -->
            <select wire:model.live="filterDepartment" class="px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>

            <!-- Role Filter -->
            <select wire:model.live="filterRole" class="px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucwords(str_replace('-', ' ', $role->name)) }}</option>
                @endforeach
            </select>

            <!-- Status Filter -->
            <select wire:model.live="filterStatus" class="px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <!-- Employment Type Filter -->
            <select wire:model.live="filterEmploymentType" class="px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all">
                <option value="">All Employment Types</option>
                <option value="permanent">Permanent</option>
                <option value="contract">Contract</option>
                <option value="temporary">Temporary</option>
            </select>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] overflow-hidden flex-1">
        @if($staff->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-[#f5f5f7] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-[17px] font-semibold text-[#1d1d1f] mb-1">No staff found</h3>
                <p class="text-[14px] text-[#86868b]">Try adjusting your filters or create a new staff member.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-black/[0.04]">
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Emp #</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Name</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Email</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Location</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Position</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Roles</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($staff as $user)
                            <tr class="hover:bg-black/[0.01] transition-colors">
                                <td class="px-6 py-3.5">
                                    <span class="text-[13px] font-mono font-medium text-[#515154]">{{ $user->employee_number ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] font-medium text-[#1d1d1f]">{{ $user->name }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] text-[#515154]">{{ $user->email }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] text-[#515154]">
                                        @if($user->primaryAssignment?->branch)
                                            {{ $user->primaryAssignment->branch->name }}
                                        @elseif($user->primaryAssignment?->department)
                                            {{ $user->primaryAssignment->department->name }}
                                        @else
                                            <span class="text-[#86868b]">Unassigned</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] text-[#515154]">{{ $user->primaryAssignment?->position ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-violet-100 text-violet-700">
                                                {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                            </span>
                                        @empty
                                            <span class="text-[12px] text-[#86868b]">No roles</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-3.5">
                                    @if($user->is_active)
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
                                    <div class="flex items-center gap-1.5">
                                        <button wire:click="editStaff({{ $user->id }})" class="inline-flex items-center px-2.5 py-1.5 text-[11px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[8px] hover:bg-black/[0.06] transition-colors">
                                            Edit
                                        </button>
                                        <button wire:click="openAssignModal({{ $user->id }})" class="inline-flex items-center px-2.5 py-1.5 text-[11px] font-semibold text-blue-600 bg-blue-50 rounded-[8px] hover:bg-blue-100 transition-colors">
                                            Assign
                                        </button>
                                        @if($user->primaryAssignment)
                                            <button wire:click="openTransferModal({{ $user->id }})" class="inline-flex items-center px-2.5 py-1.5 text-[11px] font-semibold text-green-600 bg-green-50 rounded-[8px] hover:bg-green-100 transition-colors">
                                                Transfer
                                            </button>
                                        @endif
                                        <button wire:click="showAssignmentHistory({{ $user->id }})" class="inline-flex items-center px-2.5 py-1.5 text-[11px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[8px] hover:bg-black/[0.06] transition-colors">
                                            History
                                        </button>
                                        <a href="{{ route('admin.users.roles', $user) }}" class="inline-flex items-center px-2.5 py-1.5 text-[11px] font-semibold text-violet-600 bg-violet-50 rounded-[8px] hover:bg-violet-100 transition-colors">
                                            Roles
                                        </a>
                                        <button wire:click="resetPassword({{ $user->id }})" wire:confirm="Reset password for {{ $user->name }}?" class="inline-flex items-center px-2.5 py-1.5 text-[11px] font-semibold text-red-600 bg-red-50 rounded-[8px] hover:bg-red-100 transition-colors">
                                            Reset PW
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
                {{ $staff->links() }}
            </div>
        @endif
    </div>

    <!-- ═══ Staff Create/Edit Modal ═══ -->
    @if($showStaffModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" aria-labelledby="staff-modal-title" role="dialog" aria-modal="true">
            
                <div class="fixed inset-0 bg-black/40 transition-opacity" wire:click="closeStaffModal"></div>
                <div class="relative bg-white rounded-[20px] shadow-xl w-full max-w-2xl mx-auto z-10 overflow-hidden max-h-[90vh] flex flex-col">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-black/[0.04]">
                        <h2 class="text-[20px] font-bold text-[#1d1d1f]">
                            {{ $editingUserId ? 'Edit Staff Member' : 'Create Staff Member' }}
                        </h2>
                        <p class="text-[14px] text-[#86868b] mt-0.5">
                            {{ $editingUserId ? 'Update the staff member details below.' : 'Fill in the details to create a new staff member.' }}
                        </p>
                    </div>

                    <!-- Body -->
                    <form wire:submit="saveStaff" class="flex flex-col flex-1 min-h-0">
                        <div class="px-6 py-5 overflow-y-auto space-y-5 flex-1">
                            <!-- Name & Email -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Full Name</label>
                                    <input type="text" wire:model="staffName" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="Full name" />
                                    @error('staffName') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Email</label>
                                    <input type="email" wire:model="staffEmail" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="email@example.com" />
                                    @error('staffEmail') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Employee Number & Phone -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Employee Number</label>
                                    <input type="text" wire:model="employeeNumber" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="e.g. EMP001" />
                                    @error('employeeNumber') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Phone</label>
                                    <input type="text" wire:model="staffPhone" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="Phone number" />
                                    @error('staffPhone') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">
                                    Password
                                    @if($editingUserId)
                                        <span class="text-[#86868b] font-normal">(leave blank to keep current)</span>
                                    @endif
                                </label>
                                <input type="password" wire:model="staffPassword" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="{{ $editingUserId ? 'Leave blank to keep current' : 'Minimum 8 characters' }}" />
                                @error('staffPassword') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="staffIsActive" class="sr-only peer" />
                                    <div class="w-9 h-5 bg-[#d1d1d6] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                                <span class="text-[14px] font-medium text-[#1d1d1f]">Active</span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="shrink-0 px-6 py-4 border-t border-black/[0.04] flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeStaffModal" class="px-5 py-2.5 text-[14px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[12px] hover:bg-black/[0.06] transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-[14px] font-semibold text-white bg-[#1d1d1f] rounded-[12px] hover:bg-[#333336] transition-colors shadow-sm">
                                {{ $editingUserId ? 'Update Staff' : 'Create Staff' }}
                            </button>
                        </div>
                    </form>
                </div>
        </div>
    @endif

    <!-- ═══ Assignment Modal ═══ -->
    @if($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" aria-labelledby="assign-modal-title" role="dialog" aria-modal="true">
            
                <div class="fixed inset-0 bg-black/40 transition-opacity" wire:click="closeAssignModal"></div>
                <div class="relative bg-white rounded-[20px] shadow-xl w-full max-w-2xl mx-auto z-10 overflow-hidden max-h-[90vh] flex flex-col">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-black/[0.04]">
                        <h2 class="text-[20px] font-bold text-[#1d1d1f]">Assign Staff</h2>
                        <p class="text-[14px] text-[#86868b] mt-0.5">
                            Assign <span class="font-semibold text-[#1d1d1f]">{{ $assigningUser?->name ?? '' }}</span> to a branch or department.
                        </p>
                    </div>

                    <!-- Body -->
                    <form wire:submit="saveAssignment" class="flex flex-col flex-1 min-h-0">
                        <div class="px-6 py-5 overflow-y-auto space-y-5 flex-1">
                            <!-- Branch -->
                            <div>
                                <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Branch</label>
                                <select wire:model="assignBranchId" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                    <option value="">— None —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('assignBranchId') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Department -->
                            <div>
                                <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Department</label>
                                <select wire:model="assignDepartmentId" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                    <option value="">— None —</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @error('assignDepartmentId') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <p class="text-[12px] text-[#86868b] -mt-3">Select either a branch or a department, not both.</p>

                            <!-- Position & Employment Type -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Position</label>
                                    <input type="text" wire:model="assignPosition" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="e.g. Appraiser" />
                                    @error('assignPosition') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Employment Type</label>
                                    <select wire:model="assignEmploymentType" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                        <option value="permanent">Permanent</option>
                                        <option value="contract">Contract</option>
                                        <option value="temporary">Temporary</option>
                                    </select>
                                    @error('assignEmploymentType') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Start Date & Primary -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Start Date</label>
                                    <input type="date" wire:model="assignStartDate" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" />
                                    @error('assignStartDate') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex items-end pb-1">
                                    <div class="flex items-center gap-3">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" wire:model="assignIsPrimary" class="sr-only peer" />
                                            <div class="w-9 h-5 bg-[#d1d1d6] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-500"></div>
                                        </label>
                                        <span class="text-[14px] font-medium text-[#1d1d1f]">Primary Assignment</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="shrink-0 px-6 py-4 border-t border-black/[0.04] flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeAssignModal" class="px-5 py-2.5 text-[14px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[12px] hover:bg-black/[0.06] transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-[14px] font-semibold text-white bg-blue-600 rounded-[12px] hover:bg-blue-700 transition-colors shadow-sm">
                                Save Assignment
                            </button>
                        </div>
                    </form>
                </div>
        </div>
    @endif

    <!-- ═══ Transfer Modal ═══ -->
    @if($showTransferModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" aria-labelledby="transfer-modal-title" role="dialog" aria-modal="true">
            
                <div class="fixed inset-0 bg-black/40 transition-opacity" wire:click="closeTransferModal"></div>
                <div class="relative bg-white rounded-[20px] shadow-xl w-full max-w-2xl mx-auto z-10 overflow-hidden max-h-[90vh] flex flex-col">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-black/[0.04]">
                        <h2 class="text-[20px] font-bold text-[#1d1d1f]">Transfer Staff</h2>
                        <p class="text-[14px] text-[#86868b] mt-0.5">
                            Transfer <span class="font-semibold text-[#1d1d1f]">{{ $transferringUser?->name ?? '' }}</span> to a new location.
                        </p>
                    </div>

                    @if(!$showTransferConfirm)
                        <!-- Transfer Form -->
                        <form wire:submit="confirmTransfer" class="flex flex-col flex-1 min-h-0">
                            <div class="px-6 py-5 overflow-y-auto space-y-5 flex-1">
                                <!-- Current Assignment Info -->
                                @if($currentTransferAssignment)
                                    <div class="bg-[#f5f5f7] rounded-[12px] p-4 border border-black/[0.04]">
                                        <h3 class="text-[13px] font-semibold text-[#86868b] uppercase tracking-wider mb-2">Current Assignment</h3>
                                        <div class="grid grid-cols-2 gap-3 text-[14px]">
                                            <div>
                                                <span class="text-[#86868b]">Location:</span>
                                                <span class="font-medium text-[#1d1d1f] ml-1">
                                                    {{ $currentTransferAssignment->branch?->name ?? $currentTransferAssignment->department?->name ?? 'N/A' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-[#86868b]">Type:</span>
                                                <span class="font-medium text-[#1d1d1f] ml-1">
                                                    {{ $currentTransferAssignment->branch_id ? 'Branch' : 'Department' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-[#86868b]">Position:</span>
                                                <span class="font-medium text-[#1d1d1f] ml-1">{{ $currentTransferAssignment->position }}</span>
                                            </div>
                                            <div>
                                                <span class="text-[#86868b]">Since:</span>
                                                <span class="font-medium text-[#1d1d1f] ml-1">{{ $currentTransferAssignment->started_at?->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- New Branch -->
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">New Branch</label>
                                    <select wire:model="transferBranchId" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                        <option value="">— None —</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('transferBranchId') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <!-- New Department -->
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">New Department</label>
                                    <select wire:model="transferDepartmentId" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                        <option value="">— None —</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('transferDepartmentId') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <p class="text-[12px] text-[#86868b] -mt-3">Select either a branch or a department, not both.</p>

                                <!-- New Position -->
                                <div>
                                    <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">New Position</label>
                                    <input type="text" wire:model="transferPosition" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="e.g. Senior Appraiser" />
                                    @error('transferPosition') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <!-- Reason & Effective Date -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Transfer Reason</label>
                                        <input type="text" wire:model="transferReason" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="Reason for transfer" />
                                        @error('transferReason') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Effective Date</label>
                                        <input type="date" wire:model="transferEffectiveDate" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" />
                                        @error('transferEffectiveDate') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="shrink-0 px-6 py-4 border-t border-black/[0.04] flex items-center justify-end gap-3">
                                <button type="button" wire:click="closeTransferModal" class="px-5 py-2.5 text-[14px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[12px] hover:bg-black/[0.06] transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" class="px-5 py-2.5 text-[14px] font-semibold text-white bg-green-600 rounded-[12px] hover:bg-green-700 transition-colors shadow-sm">
                                    Review Transfer
                                </button>
                            </div>
                        </form>
                    @else
                        <!-- Transfer Confirmation -->
                        <div class="px-6 py-6 space-y-5">
                            <div class="bg-green-50 border border-green-200 rounded-[12px] p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                                    <div>
                                        <h3 class="text-[15px] font-semibold text-green-800">Confirm Transfer</h3>
                                        <p class="text-[14px] text-green-700 mt-1">
                                            You are about to transfer <span class="font-semibold">{{ $transferringUser?->name }}</span>
                                            @if($currentTransferAssignment)
                                                from <span class="font-semibold">{{ $currentTransferAssignment->branch?->name ?? $currentTransferAssignment->department?->name }}</span>
                                            @endif
                                            to <span class="font-semibold">
                                                @if($transferBranchId)
                                                    {{ $branches->firstWhere('id', $transferBranchId)?->name }}
                                                @elseif($transferDepartmentId)
                                                    {{ $departments->firstWhere('id', $transferDepartmentId)?->name }}
                                                @endif
                                            </span>
                                            as <span class="font-semibold">{{ $transferPosition }}</span>
                                            effective <span class="font-semibold">{{ $transferEffectiveDate }}</span>.
                                        </p>
                                        <p class="text-[13px] text-green-600 mt-2">Reason: {{ $transferReason }}</p>
                                    </div>
                                </div>
                            </div>

                            <p class="text-[14px] text-[#515154]">
                                This will end the current assignment and create a new one. The primary assignment flag will be inherited. This action cannot be undone.
                            </p>
                        </div>

                        <div class="shrink-0 px-6 py-4 border-t border-black/[0.04] flex items-center justify-end gap-3">
                            <button type="button" wire:click="$set('showTransferConfirm', false)" class="px-5 py-2.5 text-[14px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[12px] hover:bg-black/[0.06] transition-colors">
                                Go Back
                            </button>
                            <button type="button" wire:click="executeTransfer" class="px-5 py-2.5 text-[14px] font-semibold text-white bg-red-600 rounded-[12px] hover:bg-red-700 transition-colors shadow-sm">
                                Execute Transfer
                            </button>
                        </div>
                    @endif
                </div>
        </div>
    @endif

    <!-- ═══ Assignment History Modal ═══ -->
    @if($showHistoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" aria-labelledby="history-modal-title" role="dialog" aria-modal="true">
            
                <div class="fixed inset-0 bg-black/40 transition-opacity" wire:click="closeHistoryModal"></div>
                <div class="relative bg-white rounded-[20px] shadow-xl w-full max-w-3xl mx-auto z-10 overflow-hidden max-h-[90vh] flex flex-col">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-black/[0.04]">
                        <h2 class="text-[20px] font-bold text-[#1d1d1f]">Assignment History</h2>
                        <p class="text-[14px] text-[#86868b] mt-0.5">
                            All assignments for <span class="font-semibold text-[#1d1d1f]">{{ $historyUser?->name ?? '' }}</span>
                        </p>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 max-h-[60vh] overflow-y-auto">
                        @if(count($assignmentHistory) === 0)
                            <div class="text-center py-8">
                                <p class="text-[14px] text-[#86868b]">No assignment history found.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($assignmentHistory as $assignment)
                                    <div class="flex items-start gap-4 p-4 rounded-[12px] {{ $assignment->ended_at ? 'bg-[#f5f5f7]' : 'bg-blue-50 border border-blue-100' }}">
                                        <!-- Timeline dot -->
                                        <div class="mt-1 shrink-0">
                                            @if(!$assignment->ended_at)
                                                <div class="w-3 h-3 rounded-full bg-blue-500 ring-4 ring-blue-100"></div>
                                            @else
                                                <div class="w-3 h-3 rounded-full bg-[#d1d1d6] ring-4 ring-[#f5f5f7]"></div>
                                            @endif
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[14px] font-semibold text-[#1d1d1f]">
                                                    {{ $assignment->branch?->name ?? $assignment->department?->name ?? 'Unknown' }}
                                                </span>
                                                @if($assignment->is_primary)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">PRIMARY</span>
                                                @endif
                                                @if(!$assignment->ended_at)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">ACTIVE</span>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-[13px] text-[#515154]">
                                                <span>{{ $assignment->position ?? '—' }}</span>
                                                <span>{{ ucfirst($assignment->employment_type ?? '—') }}</span>
                                                <span>
                                                    {{ $assignment->started_at?->format('d M Y') }}
                                                    @if($assignment->ended_at)
                                                        → {{ $assignment->ended_at->format('d M Y') }}
                                                    @else
                                                        → Present
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="shrink-0 px-6 py-4 border-t border-black/[0.04] flex items-center justify-end">
                        <button type="button" wire:click="closeHistoryModal" class="px-5 py-2.5 text-[14px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[12px] hover:bg-black/[0.06] transition-colors">
                            Close
                        </button>
                    </div>
                </div>
        </div>
    @endif
</div>
