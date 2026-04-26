<div>
    @if($showModal)
    <div
        x-data="{ open: true }"
        x-show="open"
        x-on:keydown.escape.window="$wire.closeModal()"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center"
    >
        {{-- Backdrop overlay --}}
        <div
            wire:click="closeModal"
            class="absolute inset-0 bg-black/30 backdrop-blur-sm"
        ></div>

        {{-- Modal panel --}}
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-md mx-4"
            style="
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.5);
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
                border-radius: 20px;
            "
        >
            <div class="p-8">
                {{-- Header --}}
                <div class="text-center mb-8">
                    <div class="w-14 h-14 mx-auto mb-4 flex items-center justify-center rounded-2xl"
                         style="background: linear-gradient(135deg, #f5f5f7 0%, #e8e8ed 100%);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1d1d1f" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold" style="color: #1d1d1f; letter-spacing: -0.01em;">
                        Sign In
                    </h2>
                    <p class="mt-1 text-sm" style="color: #86868b;">
                        Enter your credentials to access the workspace.
                    </p>
                </div>

                {{-- Login form --}}
                <form wire:submit="login" class="space-y-5">
                    {{-- Email field --}}
                    <div>
                        <label for="login-email" class="block text-sm font-medium mb-1.5" style="color: #1d1d1f;">
                            Email
                        </label>
                        <input
                            wire:model="email"
                            id="login-email"
                            type="email"
                            placeholder="admin@arrahnumation.com"
                            autocomplete="email"
                            class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-all duration-200"
                            style="
                                background: rgba(255, 255, 255, 0.9);
                                border: 1px solid rgba(0, 0, 0, 0.08);
                                color: #1d1d1f;
                            "
                            onfocus="this.style.borderColor='rgba(0,0,0,0.2)'; this.style.boxShadow='0 0 0 3px rgba(0,0,0,0.04)'"
                            onblur="this.style.borderColor='rgba(0,0,0,0.08)'; this.style.boxShadow='none'"
                        >
                        @error('email')
                            <p class="mt-1.5 text-sm" style="color: #e53e3e;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password field --}}
                    <div>
                        <label for="login-password" class="block text-sm font-medium mb-1.5" style="color: #1d1d1f;">
                            Password
                        </label>
                        <input
                            wire:model="password"
                            id="login-password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-all duration-200"
                            style="
                                background: rgba(255, 255, 255, 0.9);
                                border: 1px solid rgba(0, 0, 0, 0.08);
                                color: #1d1d1f;
                            "
                            onfocus="this.style.borderColor='rgba(0,0,0,0.2)'; this.style.boxShadow='0 0 0 3px rgba(0,0,0,0.04)'"
                            onblur="this.style.borderColor='rgba(0,0,0,0.08)'; this.style.boxShadow='none'"
                        >
                        @error('password')
                            <p class="mt-1.5 text-sm" style="color: #e53e3e;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit button --}}
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        class="w-full py-3 rounded-xl text-sm font-semibold transition-all duration-200"
                        style="
                            background: #1d1d1f;
                            color: #ffffff;
                            letter-spacing: -0.01em;
                        "
                        onmouseover="if(!this.disabled) this.style.background='#434346'"
                        onmouseout="this.style.background='#1d1d1f'"
                    >
                        <span wire:loading.remove>Sign In</span>
                        <span wire:loading class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Signing in…
                        </span>
                    </button>
                </form>

                {{-- Demo hint --}}
                <p class="mt-6 text-center text-xs" style="color: #86868b;">
                    Demo: <span class="font-medium" style="color: #1d1d1f;">admin@arrahnumation.com</span>
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
