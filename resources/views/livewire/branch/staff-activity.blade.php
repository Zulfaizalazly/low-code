<div wire:poll.120s>
    <header class="mb-10 fade-in-up">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-[34px] font-bold tracking-tight text-[#1d1d1f] mb-1 leading-tight">Staff Activity</h1>
                <p class="text-[15px] text-[#86868b] tracking-tight">Monitor staff productivity and feature usage across your branch.</p>
            </div>
            {{-- Period Toggle --}}
            <div class="flex items-center bg-white border border-[#1d1d1f]/[0.08] rounded-xl overflow-hidden shadow-sm">
                <button wire:click="setPeriod('today')" class="px-4 py-2 text-[13px] font-semibold transition-colors {{ $period === 'today' ? 'bg-[#1d1d1f] text-white' : 'text-[#86868b] hover:text-[#1d1d1f]' }}">
                    Today
                </button>
                <button wire:click="setPeriod('week')" class="px-4 py-2 text-[13px] font-semibold transition-colors {{ $period === 'week' ? 'bg-[#1d1d1f] text-white' : 'text-[#86868b] hover:text-[#1d1d1f]' }}">
                    This Week
                </button>
            </div>
        </div>
    </header>

    {{-- ─── Summary Stats ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-10">
        <div class="p-5 rounded-[20px] bg-white border border-[#1d1d1f]/[0.04] shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <p class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wide mb-2">Total Staff</p>
            <p class="text-[28px] font-bold text-[#1d1d1f] tracking-tight">{{ $totalStaff }}</p>
        </div>
        <div class="p-5 rounded-[20px] bg-white border border-emerald-500/10 shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide mb-2">Active Now</p>
            <p class="text-[28px] font-bold text-emerald-500 tracking-tight">{{ $activeStaff }}</p>
        </div>
        <div class="p-5 rounded-[20px] bg-white border border-[#1d1d1f]/[0.04] shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <p class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wide mb-2">Total Accesses</p>
            <p class="text-[28px] font-bold text-[#1d1d1f] tracking-tight">{{ $totalAccesses }}</p>
            <p class="text-[11px] text-[#86868b] mt-0.5">{{ $period === 'today' ? 'Today' : 'This week' }}</p>
        </div>
        <div class="p-5 rounded-[20px] bg-white border border-[#1d1d1f]/[0.04] shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <p class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wide mb-2">Completion Rate</p>
            <p class="text-[28px] font-bold tracking-tight {{ $completionPercent >= 90 ? 'text-emerald-500' : ($completionPercent >= 70 ? 'text-amber-500' : 'text-rose-500') }}">{{ $completionPercent }}%</p>
            <p class="text-[11px] text-[#86868b] mt-0.5">{{ $totalExecutions }} workflow executions</p>
        </div>
        <div class="p-5 rounded-[20px] bg-white border border-[#1d1d1f]/[0.04] shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <p class="text-[11px] font-semibold text-[#86868b] uppercase tracking-wide mb-2">Avg Completion Time</p>
            <p class="text-[28px] font-bold text-[#1d1d1f] tracking-tight">
                @if($avgCompletionTime >= 60)
                    {{ floor($avgCompletionTime / 60) }}m {{ round($avgCompletionTime % 60) }}s
                @else
                    {{ round($avgCompletionTime) }}s
                @endif
            </p>
            <p class="text-[11px] text-[#86868b] mt-0.5">{{ $period === 'today' ? 'Today' : 'This week' }}</p>
        </div>
    </div>

    {{-- ─── Inactive Staff Alert ─── --}}
    @if($inactiveStaff > 0)
        <div class="mb-6 px-5 py-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
            <p class="text-[13px] text-amber-700 font-medium">{{ $inactiveStaff }} staff {{ $inactiveStaff === 1 ? 'member has' : 'members have' }} not used any features in the last {{ config('branch.dashboard.inactive_staff_threshold_hours', 4) }} hours.</p>
        </div>
    @endif

    {{-- ─── Staff Activity Table ─── --}}
    <div class="bg-white border border-[#1d1d1f]/[0.06] shadow-[0_4px_20px_rgba(0,0,0,0.03)] rounded-[20px] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1d1d1f]/[0.04] bg-[#fafafa]">
            <h3 class="text-[13px] font-semibold text-[#1d1d1f] uppercase tracking-wide">Staff Members</h3>
        </div>

        <div class="divide-y divide-[#1d1d1f]/[0.04]">
            @forelse($branchStaff as $staff)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-[#fafafa] transition-colors">
                    <div class="flex items-center gap-4">
                        {{-- Avatar with status --}}
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr {{ $staff->is_active ? 'from-emerald-400 to-emerald-500' : ($staff->is_inactive ? 'from-gray-300 to-gray-400' : 'from-blue-400 to-blue-500') }} flex items-center justify-center shadow-sm">
                                <span class="text-[13px] font-bold text-white">{{ substr($staff->name, 0, 1) }}</span>
                            </div>
                            @if($staff->is_active)
                                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 rounded-full border-2 border-white"></span>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-[14px] font-semibold text-[#1d1d1f]">{{ $staff->name }}</h4>
                            <p class="text-[12px] text-[#86868b] mt-0.5">
                                @if($staff->is_active)
                                    <span class="text-emerald-600 font-medium">Active now</span>
                                    @if($staff->last_feature)
                                        — using {{ $staff->last_feature }}
                                    @endif
                                @elseif($staff->last_access)
                                    Last active {{ $staff->last_access->diffForHumans() }}
                                @else
                                    No activity recorded
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-6">
                        {{-- Today's count --}}
                        <div class="text-center">
                            <p class="text-[16px] font-bold text-[#1d1d1f]">{{ $staff->today_count }}</p>
                            <p class="text-[10px] text-[#86868b] uppercase tracking-wide font-medium">Today</p>
                        </div>
                        {{-- Week count --}}
                        <div class="text-center">
                            <p class="text-[16px] font-bold text-[#1d1d1f]">{{ $staff->week_count }}</p>
                            <p class="text-[10px] text-[#86868b] uppercase tracking-wide font-medium">Week</p>
                        </div>
                        {{-- Usage bar --}}
                        <div class="w-24">
                            @php $barWidth = $staff->week_count > 0 ? min(($staff->week_count / max($branchStaff->max('week_count'), 1)) * 100, 100) : 0; @endphp
                            <div class="w-full bg-[#f5f5f7] rounded-full h-1.5 overflow-hidden">
                                <div class="h-full rounded-full {{ $staff->is_inactive ? 'bg-gray-300' : 'bg-blue-500' }} transition-all duration-500" style="width: {{ $barWidth }}%"></div>
                            </div>
                        </div>
                        {{-- Status badge --}}
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide rounded-full {{ $staff->is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : ($staff->is_inactive ? 'bg-gray-100 text-gray-500 border border-gray-200' : 'bg-blue-50 text-blue-600 border border-blue-200') }}">
                            {{ $staff->is_active ? 'Active' : ($staff->is_inactive ? 'Inactive' : 'Idle') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <p class="text-[14px] text-[#86868b]">No staff members assigned to this branch.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ─── Staff Efficiency Metrics ─── --}}
    @if($staffEfficiency->isNotEmpty())
        <div class="mt-10 bg-white border border-[#1d1d1f]/[0.06] shadow-[0_4px_20px_rgba(0,0,0,0.03)] rounded-[20px] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#1d1d1f]/[0.04] bg-[#fafafa]">
                <h3 class="text-[13px] font-semibold text-[#1d1d1f] uppercase tracking-wide">Staff Efficiency Metrics</h3>
            </div>

            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#1d1d1f]/[0.04]">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-[#86868b] uppercase tracking-wide">Staff Member</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-[#86868b] uppercase tracking-wide">Total Executions</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-[#86868b] uppercase tracking-wide">Success Rate</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-[#86868b] uppercase tracking-wide">Avg Completion Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1d1d1f]/[0.04]">
                    @foreach($staffEfficiency as $efficiency)
                        @php
                            $staffMember = $branchStaff->firstWhere('id', $efficiency->user_id);
                            $staffName = $staffMember ? $staffMember->name : 'Unknown';
                            $successRate = round($efficiency->success_rate ?? 0, 1);
                            $avgSeconds = $efficiency->avg_completion_seconds ?? 0;
                        @endphp
                        <tr class="hover:bg-[#fafafa] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-400 to-blue-500 flex items-center justify-center shadow-sm">
                                        <span class="text-[11px] font-bold text-white">{{ substr($staffName, 0, 1) }}</span>
                                    </div>
                                    <span class="text-[14px] font-semibold text-[#1d1d1f]">{{ $staffName }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-[14px] font-bold text-[#1d1d1f]">{{ $efficiency->total_executions }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 text-[12px] font-bold rounded-full {{ $successRate >= 90 ? 'bg-emerald-50 text-emerald-600' : ($successRate >= 70 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">
                                    {{ $successRate }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-[14px] font-medium text-[#1d1d1f]">
                                    @if($avgSeconds >= 60)
                                        {{ floor($avgSeconds / 60) }}m {{ round($avgSeconds % 60) }}s
                                    @else
                                        {{ round($avgSeconds) }}s
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
