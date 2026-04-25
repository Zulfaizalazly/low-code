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
            <h1 class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">Departments</h1>
            <p class="text-[15px] text-[#86868b] mt-1">Manage organizational departments and hierarchy</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#1d1d1f] text-white text-[14px] font-semibold rounded-[12px] hover:bg-[#333336] transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Create Department
        </button>
    </div>

    <!-- Departments Tree -->
    <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] overflow-hidden min-h-[60vh] flex-1">
        @if($departments->isEmpty())
            <div class="p-12 text-center flex flex-col items-center justify-center min-h-[50vh]">
                <div class="w-16 h-16 rounded-full bg-[#f5f5f7] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
                <h3 class="text-[17px] font-semibold text-[#1d1d1f] mb-1">No departments found</h3>
                <p class="text-[14px] text-[#86868b]">Create your first department to get started.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-black/[0.04]">
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Code</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Name</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Head</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Parent</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Staff</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($departments as $department)
                            {{-- Level 0: Root department --}}
                            <tr class="hover:bg-black/[0.01] transition-colors">
                                <td class="px-6 py-3.5">
                                    <span class="text-[13px] font-mono font-medium text-[#515154]">{{ $department->code }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] font-medium text-[#1d1d1f]">{{ $department->name }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] text-[#515154]">{{ $department->head?->name ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] text-[#515154]">—</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="text-[14px] font-medium text-[#1d1d1f]">{{ $department->activeStaffAssignments->count() }}</span>
                                </td>
                                <td class="px-6 py-3.5">
                                    @if($department->is_active)
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
                                        <button wire:click="edit({{ $department->id }})" class="inline-flex items-center px-3 py-1.5 text-[12px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[8px] hover:bg-black/[0.06] transition-colors">
                                            Edit
                                        </button>
                                        <button wire:click="delete({{ $department->id }})" wire:confirm="Are you sure you want to delete this department?" class="inline-flex items-center px-3 py-1.5 text-[12px] font-semibold text-red-600 bg-red-50 rounded-[8px] hover:bg-red-100 transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Level 1: Children --}}
                            @foreach($department->children as $child)
                                <tr class="hover:bg-black/[0.01] transition-colors bg-black/[0.005]">
                                    <td class="px-6 py-3.5">
                                        <span class="ml-8 text-[13px] font-mono font-medium text-[#515154]">{{ $child->code }}</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="ml-8 text-[14px] font-medium text-[#1d1d1f] flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-[#d1d1d6]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                            {{ $child->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="text-[14px] text-[#515154]">{{ $child->head?->name ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="text-[14px] text-[#515154]">{{ $department->name }}</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="text-[14px] font-medium text-[#1d1d1f]">{{ $child->activeStaffAssignments->count() }}</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        @if($child->is_active)
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
                                            <button wire:click="edit({{ $child->id }})" class="inline-flex items-center px-3 py-1.5 text-[12px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[8px] hover:bg-black/[0.06] transition-colors">
                                                Edit
                                            </button>
                                            <button wire:click="delete({{ $child->id }})" wire:confirm="Are you sure you want to delete this department?" class="inline-flex items-center px-3 py-1.5 text-[12px] font-semibold text-red-600 bg-red-50 rounded-[8px] hover:bg-red-100 transition-colors">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Level 2: Grandchildren --}}
                                @foreach($child->children as $grandchild)
                                    <tr class="hover:bg-black/[0.01] transition-colors bg-black/[0.01]">
                                        <td class="px-6 py-3.5">
                                            <span class="ml-16 text-[13px] font-mono font-medium text-[#515154]">{{ $grandchild->code }}</span>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="ml-16 text-[14px] font-medium text-[#1d1d1f] flex items-center gap-1.5">
                                                <svg class="w-3 h-3 text-[#d1d1d6]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                {{ $grandchild->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="text-[14px] text-[#515154]">{{ $grandchild->head?->name ?? '—' }}</span>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="text-[14px] text-[#515154]">{{ $child->name }}</span>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="text-[14px] font-medium text-[#1d1d1f]">{{ $grandchild->activeStaffAssignments->count() }}</span>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @if($grandchild->is_active)
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
                                                <button wire:click="edit({{ $grandchild->id }})" class="inline-flex items-center px-3 py-1.5 text-[12px] font-semibold text-[#515154] bg-[#f5f5f7] rounded-[8px] hover:bg-black/[0.06] transition-colors">
                                                    Edit
                                                </button>
                                                <button wire:click="delete({{ $grandchild->id }})" wire:confirm="Are you sure you want to delete this department?" class="inline-flex items-center px-3 py-1.5 text-[12px] font-semibold text-red-600 bg-red-50 rounded-[8px] hover:bg-red-100 transition-colors">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Create/Edit Department Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/40 transition-opacity" wire:click="closeModal"></div>

                <!-- Modal Panel -->
                <div class="relative bg-white rounded-[20px] shadow-xl w-full max-w-2xl mx-auto z-10 overflow-hidden max-h-[90vh] flex flex-col">
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-black/[0.04]">
                        <h2 class="text-[20px] font-bold text-[#1d1d1f]">
                            {{ $editingDepartmentId ? 'Edit Department' : 'Create Department' }}
                        </h2>
                        <p class="text-[14px] text-[#86868b] mt-0.5">
                            {{ $editingDepartmentId ? 'Update the department details below.' : 'Fill in the details to create a new department.' }}
                        </p>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit="save" class="flex flex-col flex-1 min-h-0">
                        <div class="px-6 py-5 overflow-y-auto space-y-5 flex-1">
                            <!-- Code & Name -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="code" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Department Code</label>
                                    <input type="text" id="code" wire:model="code" maxlength="20" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="e.g. DEPT001" />
                                    @error('code') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="name" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Department Name</label>
                                    <input type="text" id="name" wire:model="name" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30" placeholder="Department name" />
                                    @error('name') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Description</label>
                                <textarea id="description" wire:model="description" rows="2" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 resize-none" placeholder="Brief description of the department"></textarea>
                                @error('description') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Head & Parent -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="head_id" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Department Head</label>
                                    <select id="head_id" wire:model="head_id" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                        <option value="">— No Head —</option>
                                        @foreach($heads as $head)
                                            <option value="{{ $head->id }}">{{ $head->name }} ({{ $head->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('head_id') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="parent_id" class="block text-[13px] font-semibold text-[#1d1d1f] mb-1">Parent Department</label>
                                    <select id="parent_id" wire:model="parent_id" class="w-full px-3 py-2 text-[14px] bg-[#f5f5f7] border border-black/[0.04] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30">
                                        <option value="">— None (Root Department) —</option>
                                        @foreach($allDepartments as $dept)
                                            @if($dept->id !== $editingDepartmentId)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('parent_id') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                                </div>
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
                                {{ $editingDepartmentId ? 'Update Department' : 'Create Department' }}
                            </button>
                        </div>
                    </form>
                </div>
        </div>
    @endif
</div>
