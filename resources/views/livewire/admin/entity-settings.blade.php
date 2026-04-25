<div class="space-y-6 fade-in-up flex-1 flex flex-col">
    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-[12px] text-[14px] font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Page Header -->
    <div>
        <h1 class="text-[28px] font-bold tracking-tight text-[#1d1d1f]">Entity Settings</h1>
        <p class="text-[15px] text-[#86868b] mt-1">Manage your organization's legal entity details and configuration.</p>
    </div>

    @if($showCreateForm && !$entity)
        {{-- ==================== CREATE ENTITY FORM ==================== --}}
        <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-full bg-[#f5f5f7] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#86868b]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h2 class="text-[20px] font-bold text-[#1d1d1f] mb-1">Create Your Entity</h2>
                <p class="text-[14px] text-[#86868b]">Set up the legal entity for your organization to get started.</p>
            </div>

            <div class="max-w-2xl mx-auto space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Code <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="entityCode" placeholder="e.g. ENT-001" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('entityCode') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Name <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="entityName" placeholder="Entity name" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('entityName') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Registration Number</label>
                        <input type="text" wire:model="registrationNumber" placeholder="Registration number" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('registrationNumber') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">License Number</label>
                        <input type="text" wire:model="licenseNumber" placeholder="License number" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('licenseNumber') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Address</label>
                    <textarea wire:model="address" rows="2" placeholder="Full address" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all resize-none"></textarea>
                    @error('address') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Phone</label>
                        <input type="text" wire:model="phone" placeholder="Phone number" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('phone') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" wire:model="email" placeholder="Email address" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('email') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-4">
                    <button wire:click="save" class="inline-flex items-center gap-2 px-5 py-2.5 text-[14px] font-semibold text-white bg-[#1d1d1f] rounded-[10px] hover:bg-[#333336] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Create Entity
                    </button>
                </div>
            </div>
        </div>
    @else
        {{-- ==================== ENTITY DETAILS SECTION ==================== --}}
        <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
            <div class="flex items-center justify-between p-6 border-b border-black/[0.04]">
                <div>
                    <h2 class="text-[17px] font-bold text-[#1d1d1f]">Entity Details</h2>
                    <p class="text-[13px] text-[#86868b] mt-0.5">Update your organization's legal entity information.</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($entity?->is_active)
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
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Code <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="entityCode" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('entityCode') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Name <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="entityName" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('entityName') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Registration Number</label>
                        <input type="text" wire:model="registrationNumber" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('registrationNumber') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">License Number</label>
                        <input type="text" wire:model="licenseNumber" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('licenseNumber') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Address</label>
                    <textarea wire:model="address" rows="2" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all resize-none"></textarea>
                    @error('address') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Phone</label>
                        <input type="text" wire:model="phone" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('phone') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" wire:model="email" class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.08] rounded-[10px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all" />
                        @error('email') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button wire:click="save" class="inline-flex items-center gap-2 px-5 py-2.5 text-[14px] font-semibold text-white bg-[#1d1d1f] rounded-[10px] hover:bg-[#333336] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Save Details
                    </button>
                </div>
            </div>
        </div>

        {{-- ==================== ENTITY SETTINGS SECTION ==================== --}}
        <div class="bg-white rounded-[16px] border border-black/[0.04] shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
            <div class="flex items-center justify-between p-6 border-b border-black/[0.04]">
                <div>
                    <h2 class="text-[17px] font-bold text-[#1d1d1f]">Entity Settings</h2>
                    <p class="text-[13px] text-[#86868b] mt-0.5">Configure key-value settings for this entity.</p>
                </div>
                <button wire:click="saveSettings" class="inline-flex items-center gap-2 px-4 py-2 text-[13px] font-semibold text-white bg-[#1d1d1f] rounded-[10px] hover:bg-[#333336] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Save Settings
                </button>
            </div>

            <div class="p-6 space-y-6">
                <!-- Existing Settings -->
                @if(count($settings) > 0)
                    <div class="space-y-2">
                        @foreach($settings as $key => $value)
                            <div class="flex items-center gap-3 bg-[#f5f5f7] rounded-[10px] p-3">
                                <div class="flex-1 min-w-0">
                                    <label class="block text-[12px] font-semibold text-[#86868b] uppercase tracking-wider mb-1">{{ $key }}</label>
                                    <input
                                        type="text"
                                        wire:model="settings.{{ $key }}"
                                        class="w-full px-3 py-1.5 text-[14px] bg-white border border-black/[0.04] rounded-[8px] text-[#1d1d1f] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
                                    />
                                </div>
                                <button wire:click="removeSetting('{{ $key }}')" class="shrink-0 p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-[8px] transition-colors" title="Remove setting">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center bg-[#f5f5f7] rounded-[14px]">
                        <p class="text-[14px] text-[#86868b]">No settings configured yet. Add your first setting below.</p>
                    </div>
                @endif

                <!-- Add New Setting -->
                <div class="bg-[#f5f5f7] rounded-[14px] p-5">
                    <h4 class="text-[14px] font-semibold text-[#1d1d1f] mb-3">Add New Setting</h4>
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-[12px] font-semibold text-[#86868b] mb-1">Key</label>
                            <input
                                type="text"
                                wire:model="newSettingKey"
                                placeholder="e.g. max_branches"
                                class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.04] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
                            />
                            @error('newSettingKey') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-[12px] font-semibold text-[#86868b] mb-1">Value</label>
                            <input
                                type="text"
                                wire:model="newSettingValue"
                                placeholder="e.g. 50"
                                class="w-full px-3 py-2 text-[14px] bg-white border border-black/[0.04] rounded-[10px] text-[#1d1d1f] placeholder-[#86868b] focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/30 transition-all"
                            />
                            @error('newSettingValue') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <button wire:click="addSetting" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 text-[13px] font-semibold text-[#1d1d1f] bg-white border border-black/[0.04] rounded-[10px] hover:bg-black/[0.02] transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
