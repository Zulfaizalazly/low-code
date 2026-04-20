<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-slate-200">
        <!-- Header / Stepper -->
        <div class="px-8 py-6 bg-slate-50 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">{{ $page->name }}</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $currentStep->title }}</p>

            <!-- Progress Bar -->
            <div class="mt-4 h-1.5 w-full bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600 transition-all duration-300" 
                     style="width: {{ (($currentStepIndex + 1) / count($page->steps)) * 100 }}%"></div>
            </div>
        </div>

        <!-- Form Body -->
        <div class="px-8 py-8">
            @if($isSubmitted)
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900">Success!</h3>
                    <p class="text-slate-500 mt-2">The pledge intake has been recorded and the orchestration flow is executing.</p>
                    <div class="mt-8 flex justify-center gap-4">
                        <a href="{{ route('studio.dashboard') }}" class="px-6 py-2 bg-slate-900 text-white rounded-xl text-sm font-bold shadow-lg">Return to Dashboard</a>
                        <a href="{{ route('studio.monitor') }}" class="px-6 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg">View in Monitor</a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($currentStep->fields as $field)
                        <div class="space-y-1">
                            <label for="{{ $field->field_key }}" class="block text-sm font-semibold text-slate-700">
                                {{ $field->label }}
                                @if($field->is_required)
                                    <span class="text-rose-500">*</span>
                                @endif
                            </label>
                            
                            <div class="mt-1">
                                @include('runtime.fields.' . $field->component_type, ['field' => $field])
                            </div>

                            @if($field->help_text)
                                <p class="mt-2 text-xs text-slate-500">{{ $field->help_text }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if(!$isSubmitted)
            <!-- Footer / Navigation -->
            <div class="px-8 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
                <button type="button" 
                        wire:click="back" 
                        @if($currentStepIndex === 0) disabled @endif
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 disabled:opacity-50">
                    Back
                </button>

                <button type="button" 
                        wire:click="next" 
                        class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all">
                    {{ $currentStepIndex === count($page->steps) - 1 ? 'Submit' : 'Continue' }}
                </button>
            </div>
        @endif
    </div>
</div>
