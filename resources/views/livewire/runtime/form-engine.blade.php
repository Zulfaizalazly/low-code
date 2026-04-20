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
        </div>

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
    </div>
</div>
