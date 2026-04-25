<?php

namespace App\Livewire\Admin;

use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use App\Models\Organization\StaffAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class StaffManager extends Component
{
    use WithPagination;

    // List filters
    public string $search = '';
    public string $filterBranch = '';
    public string $filterDepartment = '';
    public string $filterRole = '';
    public string $filterStatus = '';
    public string $filterEmploymentType = '';

    // Staff form
    public bool $showStaffModal = false;
    public ?int $editingUserId = null;
    public string $staffName = '';
    public string $staffEmail = '';
    public string $employeeNumber = '';
    public string $staffPhone = '';
    public string $staffPassword = '';
    public bool $staffIsActive = true;

    // Assignment form
    public bool $showAssignModal = false;
    public ?int $assigningUserId = null;
    public ?int $assignBranchId = null;
    public ?int $assignDepartmentId = null;
    public string $assignPosition = '';
    public string $assignEmploymentType = 'permanent';
    public string $assignStartDate = '';
    public bool $assignIsPrimary = true;

    // Transfer form
    public bool $showTransferModal = false;
    public ?int $transferringUserId = null;
    public ?int $transferBranchId = null;
    public ?int $transferDepartmentId = null;
    public string $transferPosition = '';
    public string $transferReason = '';
    public string $transferEffectiveDate = '';
    public bool $showTransferConfirm = false;

    // Assignment history
    public bool $showHistoryModal = false;
    public ?int $historyUserId = null;
    public $assignmentHistory = [];

    protected $paginationTheme = 'tailwind';

    // Reset page on filter changes
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterBranch() { $this->resetPage(); }
    public function updatingFilterDepartment() { $this->resetPage(); }
    public function updatingFilterRole() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterEmploymentType() { $this->resetPage(); }

    // ─── Staff CRUD ───────────────────────────────────────────

    public function createStaff()
    {
        $this->resetStaffForm();
        $this->showStaffModal = true;
    }

    public function editStaff(int $userId)
    {
        $user = User::findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->staffName = $user->name;
        $this->staffEmail = $user->email;
        $this->employeeNumber = $user->employee_number ?? '';
        $this->staffPhone = $user->phone ?? '';
        $this->staffPassword = '';
        $this->staffIsActive = $user->is_active;

        $this->showStaffModal = true;
    }

    public function saveStaff()
    {
        $rules = [
            'staffName' => 'required|string|max:255',
            'staffEmail' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ],
            'employeeNumber' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'employee_number')->ignore($this->editingUserId),
            ],
            'staffPhone' => 'nullable|string|max:20',
            'staffIsActive' => 'boolean',
        ];

        if (!$this->editingUserId) {
            $rules['staffPassword'] = 'required|string|min:8';
        } else {
            $rules['staffPassword'] = 'nullable|string|min:8';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->staffName,
            'email' => $this->staffEmail,
            'employee_number' => $this->employeeNumber,
            'phone' => $this->staffPhone ?: null,
            'is_active' => $this->staffIsActive,
            'entity_id' => auth()->user()->entity_id ?? 1,
        ];

        if ($this->staffPassword) {
            $data['password'] = Hash::make($this->staffPassword);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update($data);
            session()->flash('success', 'Staff member updated successfully.');
        } else {
            User::create($data);
            session()->flash('success', 'Staff member created successfully.');
        }

        $this->closeStaffModal();
    }

    public function resetPassword(int $userId)
    {
        $user = User::findOrFail($userId);
        $newPassword = Str::random(12);
        $user->update(['password' => Hash::make($newPassword)]);

        session()->flash('success', "Password reset for {$user->name}. New password: {$newPassword}");
    }

    public function closeStaffModal()
    {
        $this->showStaffModal = false;
        $this->resetStaffForm();
    }

    private function resetStaffForm()
    {
        $this->editingUserId = null;
        $this->staffName = '';
        $this->staffEmail = '';
        $this->employeeNumber = '';
        $this->staffPhone = '';
        $this->staffPassword = '';
        $this->staffIsActive = true;
        $this->resetErrorBag();
    }

    // ─── Assignment ───────────────────────────────────────────

    public function openAssignModal(int $userId)
    {
        $this->resetAssignForm();
        $this->assigningUserId = $userId;
        $this->assignStartDate = now()->format('Y-m-d');
        $this->showAssignModal = true;
    }

    public function saveAssignment()
    {
        $this->validate([
            'assigningUserId' => 'required|exists:users,id',
            'assignBranchId' => 'nullable|exists:branches,id',
            'assignDepartmentId' => 'nullable|exists:departments,id',
            'assignPosition' => 'required|string|max:255',
            'assignEmploymentType' => 'required|in:permanent,contract,temporary',
            'assignStartDate' => 'required|date',
            'assignIsPrimary' => 'boolean',
        ]);

        // Validate branch XOR department
        if ($this->assignBranchId && $this->assignDepartmentId) {
            $this->addError('assignBranchId', 'Assignment must be to either a branch or department, not both.');
            return;
        }

        if (!$this->assignBranchId && !$this->assignDepartmentId) {
            $this->addError('assignBranchId', 'Assignment must be to either a branch or a department.');
            return;
        }

        // Validate one primary per user
        if ($this->assignIsPrimary) {
            $existingPrimary = StaffAssignment::where('user_id', $this->assigningUserId)
                ->whereNull('ended_at')
                ->where('is_primary', true)
                ->first();

            if ($existingPrimary) {
                // End the existing primary assignment
                $existingPrimary->update(['is_primary' => false]);
            }
        }

        StaffAssignment::create([
            'user_id' => $this->assigningUserId,
            'entity_id' => auth()->user()->entity_id ?? 1,
            'branch_id' => $this->assignBranchId ?: null,
            'department_id' => $this->assignDepartmentId ?: null,
            'position' => $this->assignPosition,
            'employment_type' => $this->assignEmploymentType,
            'started_at' => $this->assignStartDate,
            'is_primary' => $this->assignIsPrimary,
        ]);

        session()->flash('success', 'Staff assignment created successfully.');
        $this->closeAssignModal();
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->resetAssignForm();
    }

    private function resetAssignForm()
    {
        $this->assigningUserId = null;
        $this->assignBranchId = null;
        $this->assignDepartmentId = null;
        $this->assignPosition = '';
        $this->assignEmploymentType = 'permanent';
        $this->assignStartDate = '';
        $this->assignIsPrimary = true;
        $this->resetErrorBag();
    }

    // ─── Assignment History ───────────────────────────────────

    public function showAssignmentHistory(int $userId)
    {
        $this->historyUserId = $userId;
        $this->assignmentHistory = StaffAssignment::where('user_id', $userId)
            ->with(['branch', 'department'])
            ->orderByDesc('started_at')
            ->get();
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->historyUserId = null;
        $this->assignmentHistory = [];
    }

    // ─── Transfer ─────────────────────────────────────────────

    public function openTransferModal(int $userId)
    {
        $this->resetTransferForm();
        $this->transferringUserId = $userId;
        $this->transferEffectiveDate = now()->format('Y-m-d');
        $this->showTransferModal = true;
    }

    public function confirmTransfer()
    {
        $this->validate([
            'transferringUserId' => 'required|exists:users,id',
            'transferBranchId' => 'nullable|exists:branches,id',
            'transferDepartmentId' => 'nullable|exists:departments,id',
            'transferPosition' => 'required|string|max:255',
            'transferReason' => 'required|string|max:500',
            'transferEffectiveDate' => 'required|date',
        ]);

        // Validate branch XOR department
        if ($this->transferBranchId && $this->transferDepartmentId) {
            $this->addError('transferBranchId', 'Transfer must be to either a branch or department, not both.');
            return;
        }

        if (!$this->transferBranchId && !$this->transferDepartmentId) {
            $this->addError('transferBranchId', 'Transfer must be to either a branch or a department.');
            return;
        }

        // Validate destination differs from current
        $currentAssignment = StaffAssignment::where('user_id', $this->transferringUserId)
            ->whereNull('ended_at')
            ->where('is_primary', true)
            ->first();

        if ($currentAssignment) {
            $sameLocation = false;
            if ($this->transferBranchId && $currentAssignment->branch_id == $this->transferBranchId) {
                $sameLocation = true;
            }
            if ($this->transferDepartmentId && $currentAssignment->department_id == $this->transferDepartmentId) {
                $sameLocation = true;
            }

            if ($sameLocation) {
                $this->addError('transferBranchId', 'Transfer destination must differ from current assignment.');
                return;
            }
        }

        $this->showTransferConfirm = true;
    }

    public function executeTransfer()
    {
        $currentAssignment = StaffAssignment::where('user_id', $this->transferringUserId)
            ->whereNull('ended_at')
            ->where('is_primary', true)
            ->first();

        if (!$currentAssignment) {
            session()->flash('error', 'No active primary assignment found for this staff member.');
            $this->closeTransferModal();
            return;
        }

        DB::transaction(function () use ($currentAssignment) {
            // End current assignment
            $currentAssignment->update([
                'ended_at' => $this->transferEffectiveDate,
            ]);

            // Create new assignment inheriting primary flag
            StaffAssignment::create([
                'user_id' => $this->transferringUserId,
                'entity_id' => $currentAssignment->entity_id,
                'branch_id' => $this->transferBranchId ?: null,
                'department_id' => $this->transferDepartmentId ?: null,
                'position' => $this->transferPosition,
                'employment_type' => $currentAssignment->employment_type,
                'started_at' => $this->transferEffectiveDate,
                'is_primary' => $currentAssignment->is_primary,
            ]);
        });

        session()->flash('success', 'Staff transfer completed successfully.');
        $this->closeTransferModal();
    }

    public function closeTransferModal()
    {
        $this->showTransferModal = false;
        $this->showTransferConfirm = false;
        $this->resetTransferForm();
    }

    private function resetTransferForm()
    {
        $this->transferringUserId = null;
        $this->transferBranchId = null;
        $this->transferDepartmentId = null;
        $this->transferPosition = '';
        $this->transferReason = '';
        $this->transferEffectiveDate = '';
        $this->showTransferConfirm = false;
        $this->resetErrorBag();
    }

    // ─── Render ───────────────────────────────────────────────

    public function render()
    {
        $query = User::query()
            ->with(['primaryAssignment.branch', 'primaryAssignment.department', 'roles'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%')
                       ->orWhere('employee_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterBranch !== '', fn ($q) =>
                $q->whereHas('primaryAssignment', fn ($sq) =>
                    $sq->where('branch_id', $this->filterBranch)
                )
            )
            ->when($this->filterDepartment !== '', fn ($q) =>
                $q->whereHas('primaryAssignment', fn ($sq) =>
                    $sq->where('department_id', $this->filterDepartment)
                )
            )
            ->when($this->filterRole !== '', fn ($q) =>
                $q->role($this->filterRole)
            )
            ->when($this->filterStatus !== '', fn ($q) =>
                $q->where('is_active', $this->filterStatus === 'active')
            )
            ->when($this->filterEmploymentType !== '', fn ($q) =>
                $q->whereHas('primaryAssignment', fn ($sq) =>
                    $sq->where('employment_type', $this->filterEmploymentType)
                )
            );

        $staff = $query->orderBy('name')->paginate(15);

        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        // Get current assignment for transfer modal
        $currentTransferAssignment = null;
        if ($this->transferringUserId) {
            $currentTransferAssignment = StaffAssignment::where('user_id', $this->transferringUserId)
                ->whereNull('ended_at')
                ->where('is_primary', true)
                ->with(['branch', 'department'])
                ->first();
        }

        // Get user being assigned for display
        $assigningUser = $this->assigningUserId ? User::find($this->assigningUserId) : null;
        $transferringUser = $this->transferringUserId ? User::find($this->transferringUserId) : null;
        $historyUser = $this->historyUserId ? User::find($this->historyUserId) : null;

        return view('livewire.admin.staff-manager', [
            'staff' => $staff,
            'branches' => $branches,
            'departments' => $departments,
            'roles' => $roles,
            'currentTransferAssignment' => $currentTransferAssignment,
            'assigningUser' => $assigningUser,
            'transferringUser' => $transferringUser,
            'historyUser' => $historyUser,
        ])->layout('layouts.admin');
    }
}
