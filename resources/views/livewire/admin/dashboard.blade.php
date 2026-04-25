<div class="space-y-8 fade-in-up flex-1 flex flex-col">
    <!-- Page Header -->
    <div>
        <h1 class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">Dashboard</h1>
        <p class="text-[15px] text-[#86868b] mt-1">Organization overview and key metrics</p>
    </div>

    @if($branchesWithStats->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white rounded-[20px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-[#f5f5f7] flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h3 class="text-[17px] font-semibold text-[#1d1d1f] mb-1">No branches yet</h3>
            <p class="text-[14px] text-[#86868b] mb-6">Get started by creating your first branch to build your organizational structure.</p>
            <a href="{{ route('admin.branches') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#1d1d1f] text-white text-[14px] font-semibold rounded-[12px] hover:bg-[#333336] transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Create your first branch
            </a>
        </div>
    @else
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Active Branches --}}
            <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-[10px] bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <span class="text-[13px] font-medium text-[#86868b]">Active Branches</span>
                </div>
                <p class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">{{ $activeBranchCount }}</p>
            </div>

            {{-- Total Staff --}}
            <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-[10px] bg-green-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <span class="text-[13px] font-medium text-[#86868b]">Total Staff</span>
                </div>
                <p class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">{{ $totalStaffCount }}</p>
            </div>

            {{-- Departments --}}
            <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-[10px] bg-purple-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    </div>
                    <span class="text-[13px] font-medium text-[#86868b]">Departments</span>
                </div>
                <p class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">{{ $departmentCount }}</p>
            </div>

            {{-- Regions --}}
            <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-[10px] bg-orange-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-[13px] font-medium text-[#86868b]">Regions</span>
                </div>
                <p class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">{{ $regionCount }}</p>
            </div>
        </div>

        {{-- Breakdowns Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Branch Type Breakdown --}}
            <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-6">
                <h3 class="text-[15px] font-semibold text-[#1d1d1f] mb-4">Branch Types</h3>
                <div class="space-y-3">
                    @php
                        $typeLabels = ['hq' => 'Headquarters', 'branch' => 'Branch', 'mini_branch' => 'Mini Branch'];
                        $typeColors = ['hq' => 'bg-blue-100 text-blue-700', 'branch' => 'bg-green-100 text-green-700', 'mini_branch' => 'bg-amber-100 text-amber-700'];
                    @endphp
                    @foreach(['hq', 'branch', 'mini_branch'] as $type)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-semibold {{ $typeColors[$type] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $typeLabels[$type] ?? ucfirst($type) }}
                                </span>
                            </div>
                            <span class="text-[15px] font-semibold text-[#1d1d1f]">{{ $branchTypeBreakdown[$type] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Employment Type Breakdown --}}
            <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-6">
                <h3 class="text-[15px] font-semibold text-[#1d1d1f] mb-4">Staff by Employment Type</h3>
                <div class="space-y-3">
                    @php
                        $empLabels = ['permanent' => 'Permanent', 'contract' => 'Contract', 'temporary' => 'Temporary'];
                        $empColors = ['permanent' => 'bg-emerald-100 text-emerald-700', 'contract' => 'bg-sky-100 text-sky-700', 'temporary' => 'bg-rose-100 text-rose-700'];
                    @endphp
                    @foreach(['permanent', 'contract', 'temporary'] as $empType)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-semibold {{ $empColors[$empType] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $empLabels[$empType] ?? ucfirst($empType) }}
                                </span>
                            </div>
                            <span class="text-[15px] font-semibold text-[#1d1d1f]">{{ $employmentTypeBreakdown[$empType] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Branches Table --}}
        <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="px-6 py-4 border-b border-black/[0.04]">
                <h3 class="text-[15px] font-semibold text-[#1d1d1f]">Branches</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-black/[0.04]">
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Name</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Type</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Manager</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Staff</th>
                            <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04]">
                        @foreach($branchesWithStats as $branch)
                            <tr class="hover:bg-black/[0.01] transition-colors">
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Staff Assignments --}}
        @if($recentAssignments->isNotEmpty())
            <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] overflow-hidden">
                <div class="px-6 py-4 border-b border-black/[0.04]">
                    <h3 class="text-[15px] font-semibold text-[#1d1d1f]">Recent Staff Assignments</h3>
                    <p class="text-[13px] text-[#86868b] mt-0.5">Created or updated in the last 30 days</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-black/[0.04]">
                                <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Staff</th>
                                <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Location</th>
                                <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Position</th>
                                <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Type</th>
                                <th class="text-left px-6 py-3 text-[12px] font-semibold text-[#86868b] uppercase tracking-wider">Updated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04]">
                            @foreach($recentAssignments as $assignment)
                                <tr class="hover:bg-black/[0.01] transition-colors">
                                    <td class="px-6 py-3.5">
                                        <span class="text-[14px] font-medium text-[#1d1d1f]">{{ $assignment->user?->name ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="text-[14px] text-[#515154]">{{ $assignment->location_name }}</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="text-[14px] text-[#515154]">{{ $assignment->position ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="text-[14px] text-[#515154] capitalize">{{ $assignment->employment_type ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="text-[13px] text-[#86868b]">{{ $assignment->updated_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
