<div class="flex-1 flex flex-col fade-in-up">
    {{-- Header --}}
    <div class="flex items-end justify-between mb-8">
        <div>
            <p class="text-[11px] font-semibold tracking-[0.12em] uppercase text-[#86868b] mb-1">Admin Console</p>
            <h1 class="text-[32px] font-extrabold tracking-tight text-[#1d1d1f] leading-none">Organization</h1>
        </div>
        <div class="flex items-center gap-2 text-[12px] text-[#86868b]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Live &middot; {{ now()->format('d M Y, g:i A') }}
        </div>
    </div>

    @if($branchesWithStats->isEmpty())
        {{-- Empty State --}}
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center max-w-sm">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200/60 flex items-center justify-center mx-auto mb-5 shadow-sm">
                    <svg class="w-9 h-9 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-[18px] font-bold text-[#1d1d1f] mb-1.5">No branches configured</h3>
                <p class="text-[14px] text-[#86868b] leading-relaxed mb-6">Set up your first branch to start building the organizational structure.</p>
                <a href="{{ route('admin.branches') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#1d1d1f] text-white text-[13px] font-semibold rounded-xl hover:bg-[#333] transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Create Branch
                </a>
            </div>
        </div>
    @else
        {{-- KPI Strip --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            @php
                $kpis = [
                    ['label' => 'Branches', 'value' => $activeBranchCount, 'sub' => 'active', 'color' => 'blue'],
                    ['label' => 'Staff', 'value' => $totalStaffCount, 'sub' => 'total', 'color' => 'emerald'],
                    ['label' => 'Departments', 'value' => $departmentCount, 'sub' => 'active', 'color' => 'violet'],
                    ['label' => 'Regions', 'value' => $regionCount, 'sub' => 'coverage', 'color' => 'amber'],
                ];
            @endphp
            @foreach($kpis as $kpi)
                <div class="relative overflow-hidden bg-white rounded-2xl border border-black/[0.04] p-5 group hover:border-{{ $kpi['color'] }}-200/60 transition-colors">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-{{ $kpi['color'] }}-500/[0.03] rounded-full -translate-y-6 translate-x-6"></div>
                    <p class="text-[36px] font-extrabold tracking-tight text-[#1d1d1f] leading-none">{{ $kpi['value'] }}</p>
                    <div class="flex items-baseline gap-1.5 mt-2">
                        <span class="text-[13px] font-semibold text-[#1d1d1f]">{{ $kpi['label'] }}</span>
                        <span class="text-[11px] text-[#86868b]">{{ $kpi['sub'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Composition Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-3 mb-6">
            {{-- Branch Composition --}}
            <div class="lg:col-span-3 bg-white rounded-2xl border border-black/[0.04] p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[13px] font-bold text-[#1d1d1f] tracking-tight">Branch Composition</h3>
                    <span class="text-[11px] text-[#86868b]">{{ array_sum($branchTypeBreakdown) }} total</span>
                </div>
                @php
                    $typeConfig = [
                        'hq'          => ['label' => 'HQ',          'bg' => 'bg-blue-600',   'light' => 'bg-blue-50 text-blue-700'],
                        'branch'      => ['label' => 'Branch',      'bg' => 'bg-emerald-500', 'light' => 'bg-emerald-50 text-emerald-700'],
                        'mini_branch' => ['label' => 'Mini Branch', 'bg' => 'bg-amber-500',   'light' => 'bg-amber-50 text-amber-700'],
                    ];
                    $totalBranches = max(array_sum($branchTypeBreakdown), 1);
                @endphp
                {{-- Stacked bar --}}
                <div class="flex rounded-lg overflow-hidden h-2.5 mb-4 bg-slate-100">
                    @foreach(['hq', 'branch', 'mini_branch'] as $type)
                        @if(($branchTypeBreakdown[$type] ?? 0) > 0)
                            <div class="{{ $typeConfig[$type]['bg'] }} transition-all" style="width: {{ ($branchTypeBreakdown[$type] / $totalBranches) * 100 }}%"></div>
                        @endif
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-2">
                    @foreach(['hq', 'branch', 'mini_branch'] as $type)
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $typeConfig[$type]['bg'] }}"></span>
                            <span class="text-[12px] text-[#515154]">{{ $typeConfig[$type]['label'] }}</span>
                            <span class="text-[13px] font-bold text-[#1d1d1f]">{{ $branchTypeBreakdown[$type] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Employment Breakdown --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-black/[0.04] p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[13px] font-bold text-[#1d1d1f] tracking-tight">Employment Mix</h3>
                    <span class="text-[11px] text-[#86868b]">{{ array_sum($employmentTypeBreakdown) }} staff</span>
                </div>
                @php
                    $empConfig = [
                        'permanent' => ['label' => 'Permanent', 'dot' => 'bg-emerald-500'],
                        'contract'  => ['label' => 'Contract',  'dot' => 'bg-sky-500'],
                        'temporary' => ['label' => 'Temporary', 'dot' => 'bg-rose-400'],
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach(['permanent', 'contract', 'temporary'] as $empType)
                        @php $empVal = $employmentTypeBreakdown[$empType] ?? 0; @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-2 h-2 rounded-full {{ $empConfig[$empType]['dot'] }} shrink-0"></span>
                                <span class="text-[13px] text-[#515154] truncate">{{ $empConfig[$empType]['label'] }}</span>
                            </div>
                            <span class="text-[15px] font-bold text-[#1d1d1f] tabular-nums ml-3">{{ $empVal }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Branches Table --}}
        <div class="bg-white rounded-2xl border border-black/[0.04] overflow-hidden flex-1 flex flex-col">
            <div class="px-5 py-4 flex items-center justify-between border-b border-black/[0.04]">
                <div class="flex items-center gap-3">
                    <h3 class="text-[13px] font-bold text-[#1d1d1f] tracking-tight">All Branches</h3>
                    <span class="text-[11px] font-medium text-[#86868b] bg-black/[0.03] px-2 py-0.5 rounded-md">{{ $branchesWithStats->count() }}</span>
                </div>
                <a href="{{ route('admin.branches') }}" class="text-[12px] font-semibold text-blue-600 hover:text-blue-700 transition-colors">Manage →</a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full">
                    <thead>
                        <tr class="bg-[#fafafa]">
                            <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Branch</th>
                            <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Type</th>
                            <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Manager</th>
                            <th class="text-right px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Staff</th>
                            <th class="text-right px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.03]">
                        @foreach($branchesWithStats as $branch)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                                            <span class="text-[11px] font-bold text-slate-500">{{ strtoupper(substr($branch->name, 0, 2)) }}</span>
                                        </div>
                                        <span class="text-[13px] font-semibold text-[#1d1d1f]">{{ $branch->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    @php
                                        $tBadge = match($branch->type) {
                                            'hq'          => ['HQ',          'bg-blue-600 text-white'],
                                            'branch'      => ['Branch',      'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/60'],
                                            'mini_branch' => ['Mini',        'bg-amber-50 text-amber-700 ring-1 ring-amber-200/60'],
                                            default       => [ucfirst($branch->type), 'bg-gray-100 text-gray-600'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide {{ $tBadge[1] }}">{{ $tBadge[0] }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    @if($branch->manager)
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center shrink-0">
                                                <span class="text-[8px] font-bold text-white">{{ strtoupper(substr($branch->manager->name, 0, 1)) }}</span>
                                            </div>
                                            <span class="text-[13px] text-[#515154]">{{ $branch->manager->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-[12px] text-[#c7c7cc] italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="text-[14px] font-bold text-[#1d1d1f] tabular-nums">{{ $branch->active_staff_assignments_count }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if($branch->is_active)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#c7c7cc]">
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

        {{-- Recent Assignments --}}
        @if($recentAssignments->isNotEmpty())
            <div class="bg-white rounded-2xl border border-black/[0.04] overflow-hidden mt-3">
                <div class="px-5 py-4 flex items-center justify-between border-b border-black/[0.04]">
                    <div class="flex items-center gap-3">
                        <h3 class="text-[13px] font-bold text-[#1d1d1f] tracking-tight">Recent Assignments</h3>
                        <span class="text-[11px] text-[#86868b]">Last 30 days</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-[#fafafa]">
                                <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Staff</th>
                                <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Location</th>
                                <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Position</th>
                                <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Type</th>
                                <th class="text-right px-5 py-2.5 text-[11px] font-semibold text-[#86868b] uppercase tracking-wider">Updated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.03]">
                            @foreach($recentAssignments as $assignment)
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center shrink-0">
                                                <span class="text-[9px] font-bold text-white">{{ strtoupper(substr($assignment->user?->name ?? '?', 0, 1)) }}</span>
                                            </div>
                                            <span class="text-[13px] font-medium text-[#1d1d1f]">{{ $assignment->user?->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-[13px] text-[#515154]">{{ $assignment->location_name }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-[13px] text-[#515154]">{{ $assignment->position ?? '—' }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-[12px] text-[#515154] capitalize">{{ $assignment->employment_type ?? '—' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="text-[12px] text-[#86868b] tabular-nums">{{ $assignment->updated_at->diffForHumans(short: true) }}</span>
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
