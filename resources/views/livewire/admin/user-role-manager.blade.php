<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">Manage Roles & Permissions: {{ $user->name }}</h2>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Roles Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Roles</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($roles as $role)
                    <label class="flex items-center space-x-2 p-3 border rounded hover:bg-gray-50 cursor-pointer">
                        <input 
                            type="checkbox" 
                            wire:model="selectedRoles" 
                            value="{{ $role->name }}"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="text-sm font-medium">{{ ucwords(str_replace(['_', '-'], ' ', $role->name)) }}</span>
                    </label>
                @endforeach
            </div>
            <button 
                wire:click="updateRoles" 
                class="mt-4 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700"
            >
                Update Roles
            </button>
        </div>

        <!-- Current Permissions (via Roles) -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Current Permissions (via Roles)</h3>
            <div class="bg-gray-50 p-4 rounded">
                @if($userPermissions->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($userPermissions as $permission)
                            <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full">
                                {{ $permission->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No permissions assigned</p>
                @endif
            </div>
        </div>

        <!-- Direct Permissions Section -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Direct Permissions (Override)</h3>
            <p class="text-sm text-gray-600 mb-4">
                Direct permissions override role permissions. Use sparingly.
            </p>
            
            @foreach($permissionsByCategory as $category => $permissions)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">{{ $category }}</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($permissions as $permission)
                            <label class="flex items-center space-x-2 text-sm">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedPermissions" 
                                    value="{{ $permission->name }}"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span>{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <button 
                wire:click="updatePermissions" 
                class="mt-4 bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700"
            >
                Update Direct Permissions
            </button>
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ url()->previous() }}" class="text-blue-600 hover:underline">
                ← Back to User List
            </a>
        </div>
    </div>
</div>
