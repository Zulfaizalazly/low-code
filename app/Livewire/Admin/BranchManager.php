<?php

namespace App\Livewire\Admin;

use App\Models\Organization\Branch;
use App\Models\Organization\Region;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class BranchManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterRegion = '';
    public string $filterState = '';
    public string $filterType = '';
    public string $filterStatus = '';

    // Modal state
    public bool $showModal = false;
    public ?int $editingBranchId = null;

    // Form fields
    public string $code = '';
    public string $name = '';
    public string $type = 'branch';
    public ?int $region_id = null;
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $postcode = '';
    public string $phone = '';
    public string $email = '';
    public ?int $manager_id = null;
    public array $opening_hours = [];
    public bool $is_active = true;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRegion()
    {
        $this->resetPage();
    }

    public function updatingFilterState()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $branchId)
    {
        $branch = Branch::findOrFail($branchId);

        $this->editingBranchId = $branch->id;
        $this->code = $branch->code;
        $this->name = $branch->name;
        $this->type = $branch->type;
        $this->region_id = $branch->region_id;
        $this->address = $branch->address ?? '';
        $this->city = $branch->city ?? '';
        $this->state = $branch->state ?? '';
        $this->postcode = $branch->postcode ?? '';
        $this->phone = $branch->phone ?? '';
        $this->email = $branch->email ?? '';
        $this->manager_id = $branch->manager_id;
        $this->opening_hours = $branch->opening_hours ?? [];
        $this->is_active = $branch->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $entityId = auth()->user()->entity_id ?? 1;

        $rules = [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('branches', 'code')
                    ->where('entity_id', $entityId)
                    ->ignore($this->editingBranchId),
            ],
            'name' => 'required|string|max:255',
            'type' => 'required|in:hq,branch,mini_branch',
            'region_id' => 'nullable|exists:regions,id',
            'address' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'opening_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ];

        $this->validate($rules);

        // Validate only one HQ per entity
        if ($this->type === 'hq') {
            $existingHq = Branch::where('entity_id', $entityId)
                ->where('type', 'hq')
                ->when($this->editingBranchId, fn ($q) => $q->where('id', '!=', $this->editingBranchId))
                ->exists();

            if ($existingHq) {
                $this->addError('type', 'An HQ branch already exists for this entity.');
                return;
            }
        }

        // Validate manager has branch_manager role
        if ($this->manager_id) {
            $manager = User::find($this->manager_id);
            if ($manager && !$manager->hasRole('branch_manager')) {
                $this->addError('manager_id', 'The selected manager must have the branch_manager role.');
                return;
            }
        }

        $data = [
            'entity_id' => $entityId,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'region_id' => $this->region_id,
            'address' => $this->address ?: null,
            'city' => $this->city,
            'state' => $this->state,
            'postcode' => $this->postcode ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'manager_id' => $this->manager_id,
            'opening_hours' => $this->opening_hours ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingBranchId) {
            $branch = Branch::findOrFail($this->editingBranchId);
            $branch->update($data);
            session()->flash('success', 'Branch updated successfully.');
        } else {
            Branch::create($data);
            session()->flash('success', 'Branch created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(int $branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $branch->update(['is_active' => !$branch->is_active]);

        $status = $branch->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Branch {$status} successfully.");
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->editingBranchId = null;
        $this->code = '';
        $this->name = '';
        $this->type = 'branch';
        $this->region_id = null;
        $this->address = '';
        $this->city = '';
        $this->state = '';
        $this->postcode = '';
        $this->phone = '';
        $this->email = '';
        $this->manager_id = null;
        $this->opening_hours = [];
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = Branch::query()
            ->with(['region', 'manager', 'activeStaffAssignments'])
            ->withCount('activeStaffAssignments');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterRegion !== '') {
            $query->where('region_id', $this->filterRegion);
        }

        if ($this->filterState !== '') {
            $query->where('state', $this->filterState);
        }

        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus === 'active');
        }

        $branches = $query->orderBy('name')->paginate(15);

        $regions = Region::orderBy('name')->get();

        $states = Branch::query()
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        $managers = User::role('branch_manager')->orderBy('name')->get();

        return view('livewire.admin.branch-manager', [
            'branches' => $branches,
            'regions' => $regions,
            'states' => $states,
            'managers' => $managers,
        ])->layout('layouts.admin');
    }
}
