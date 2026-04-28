<div class="fade-in-up">
    @if(!$page)
        <div class="bg-white rounded-[24px] p-12 text-center border border-black/[0.04] shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
            <div class="w-14 h-14 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
            </div>
            <p class="text-[15px] font-medium text-[#86868b]">Feature not available. Redirecting...</p>
        </div>
    @else
    <div class="bg-white rounded-[24px] border border-black/[0.04] shadow-[0_4px_24px_rgb(0,0,0,0.03)]">
        <!-- Header -->
        <div class="px-8 pt-8 pb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-8 h-8 rounded-[10px] bg-gradient-to-br from-green-500 to-emerald-600 shadow-sm shadow-green-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h2 class="text-[22px] font-bold tracking-tight text-[#1d1d1f]">{{ $page->name }}</h2>
            </div>

            <!-- Step Indicator -->
            <div class="mt-6 bg-[#f9f9fb] rounded-[16px] border border-black/[0.04] px-6 py-4">
                <div class="flex items-center justify-between max-w-2xl mx-auto">
                    @foreach($page->steps as $idx => $step)
                        <div class="flex items-center {{ $idx < count($page->steps) - 1 ? 'flex-1' : '' }}">
                            <div class="flex items-center gap-2 {{ $idx <= $currentStepIndex ? '' : 'opacity-40' }}">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0 transition-all duration-300
                                    {{ $idx < $currentStepIndex ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-500/20' : ($idx === $currentStepIndex ? 'bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-sm shadow-green-500/20' : 'bg-white text-[#86868b] border border-black/[0.08]') }}">
                                    @if($idx < $currentStepIndex)
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </div>
                                <span class="text-[12px] font-semibold {{ $idx === $currentStepIndex ? 'text-[#1d1d1f]' : 'text-[#86868b]' }} hidden sm:inline whitespace-nowrap">{{ $step->title }}</span>
                            </div>
                            @if($idx < count($page->steps) - 1)
                                <div class="flex-1 h-[2px] rounded-full {{ $idx < $currentStepIndex ? 'bg-emerald-300' : 'bg-black/[0.06]' }} transition-all duration-300 mx-3 min-w-[20px]"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="h-px bg-gradient-to-r from-transparent via-black/[0.06] to-transparent"></div>

        <!-- Form Body -->
        <div class="px-8 py-8">
            @if($isSubmitted)
                <div class="text-center py-10 fade-in-up">
                    <div class="w-20 h-20 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/20">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-[26px] font-bold text-[#1d1d1f] tracking-tight">Submitted Successfully</h3>
                    <p class="text-[15px] text-[#86868b] mt-2 max-w-sm mx-auto">The transaction has been recorded and the workflow is now processing.</p>
                    <div class="mt-8 flex justify-center gap-3">
                        <a href="{{ route('runtime.portal') }}" class="px-5 py-2.5 bg-[#1d1d1f] text-white rounded-[12px] text-[13px] font-bold shadow-lg hover:bg-[#434346] transition-colors">
                            Back to Portal
                        </a>
                        <a href="{{ route('portal.operations.launch', ['featureKey' => $this->featureKey]) }}" class="px-5 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-[12px] text-[13px] font-bold shadow-lg shadow-green-500/20 hover:shadow-green-500/30 transition-all">
                            Start New
                        </a>
                        @if(auth()->user()?->hasRole('branch_manager') && session('branch_view_mode') === 'staff')
                            <a href="{{ route('branch.dashboard') }}" class="px-5 py-2.5 bg-emerald-600 text-white rounded-[12px] text-[13px] font-bold shadow-lg hover:bg-emerald-700 transition-colors">Return to Ops</a>
                        @endif
                    </div>
                </div>
            @else
                <!-- Current Step Title -->
                <div class="mb-8">
                    <h3 class="text-[18px] font-bold text-[#1d1d1f] tracking-tight">{{ $currentStep?->title }}</h3>
                    @if($currentStep?->description)
                        <p class="text-[14px] text-[#86868b] mt-1">{{ $currentStep->description }}</p>
                    @endif
                </div>

                <div class="space-y-6">
                    @foreach($currentStep->fields as $field)
                        <div>
                            <label for="{{ $field->field_key }}" class="block text-[13px] font-semibold text-[#1d1d1f] mb-2">
                                {{ $field->label }}
                                @if($field->is_required)
                                    <span class="text-green-500 ml-0.5">*</span>
                                @endif
                            </label>

                            <div>
                                @include('runtime.fields.' . $field->component_type, ['field' => $field])
                            </div>

                            @if($field->help_text)
                                <p class="mt-2 text-[12px] text-[#86868b]">{{ $field->help_text }}</p>
                            @endif

                            @error("formData.{$field->field_key}")
                                <p class="mt-1.5 text-[12px] text-rose-500 font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01" /></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if(!$isSubmitted)
            <div class="h-px bg-gradient-to-r from-transparent via-black/[0.06] to-transparent"></div>

            <!-- Footer -->
            <div class="px-8 py-5 flex justify-between items-center">
                <button type="button"
                        wire:click="back"
                        @if($currentStepIndex === 0) disabled @endif
                        class="flex items-center gap-2 px-4 py-2.5 text-[13px] font-semibold text-[#515154] hover:text-[#1d1d1f] disabled:opacity-30 disabled:cursor-not-allowed transition-colors rounded-[10px] hover:bg-black/[0.03]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Back
                </button>

                <button type="button"
                        wire:click="next"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-[13px] font-bold rounded-[12px] shadow-lg shadow-green-500/20 hover:shadow-green-500/30 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="next">
                        {{ $currentStepIndex === count($page->steps) - 1 ? 'Submit' : 'Continue' }}
                    </span>
                    <span wire:loading wire:target="next" class="flex items-center gap-2">
                        Processing...
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </span>
                    <svg wire:loading.remove wire:target="next" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
        @endif
    </div>
    @endif
</div>
