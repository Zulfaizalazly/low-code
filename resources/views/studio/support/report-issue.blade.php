<div class="min-h-screen bg-slate-900 p-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white/5 border border-white/10 rounded-2xl p-8 backdrop-blur-sm">
            <h1 class="text-2xl font-bold text-white mb-2">Report AI Generation Issue</h1>
            <p class="text-slate-400 mb-8">Help us improve by reporting issues you encounter.</p>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('studio.support.submit-report') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Issue Title</label>
                    <input 
                        type="text" 
                        name="title" 
                        required
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500"
                        placeholder="Brief description of the issue"
                    >
                    @error('title')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Description</label>
                    <textarea 
                        name="description" 
                        required
                        rows="6"
                        class="w-full bg-slate-800 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500"
                        placeholder="What happened? What were you trying to do?"
                    ></textarea>
                    @error('description')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if(!empty($context))
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Error Context (Auto-captured)</label>
                        <div class="bg-slate-800 border border-white/10 rounded-lg p-4 text-xs text-slate-400 font-mono overflow-x-auto">
                            <pre>{{ json_encode($context, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <input type="hidden" name="context" value="{{ json_encode($context) }}">
                    </div>
                @endif

                <div class="flex gap-4">
                    <button 
                        type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition-all"
                    >
                        Submit Report
                    </button>
                    <a 
                        href="{{ url()->previous() }}"
                        class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-lg transition-all"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
