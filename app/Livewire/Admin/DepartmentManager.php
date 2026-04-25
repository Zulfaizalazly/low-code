<?php

namespace App\Livewire\Admin;

use App\Models\Organization\Department;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DepartmentManager extends Component
{
    // Modal state
    public bool $showModal = false;
    public ?int $editingDepartmentId = null;

    // Form fields
    public string $code = '';
    public string $name = '';
    public string $description = '';
    public ?int $head_id = null;
    public ?int $parent_id = null;
    public bool $is_active = true;

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $departmentId)
    {
        $department = Department::findOrFail($departmentId);

        $this->editingDepartmentId = $department->id;
        $this->code = $department->code;
        $this->name = $department->name;
        $this->description = $department->description ?? '';
        $this->head_id = $department->head_id;
        $this->parent_id = $department->parent_id;
        $this->is_active = $department->is_active;

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
                Rule::unique('departments', 'code')
                    ->where('entity_id', $entityId)
                    ->ignore($this->editingDepartmentId),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ];

        $this->validate($rules);

        $data = [
            'entity_id' => $entityId,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'head_id' => $this->head_id,
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
        ];

        if ($this->editingDepartmentId) {
            $department = Department::findOrFail($this->editingDepartmentId);
            $department->update($data);
            session()->flash('success', 'Department updated successfully.');
        } else {
            Department::create($data);
            session()->flash('success', 'Department created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $departmentId)
    {
        $department = Department::findOrFail($departmentId);

        if ($department->activeStaffAssignments()->count() > 0) {
            session()->flash('error', 'Cannot delete department with active staff assignments.');
            return;
        }

        $department->delete();
        session()->flash('success', 'Department deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->editingDepartmentId = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->head_id = null;
        $this->parent_id = null;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $departments = Department::root()
            ->with([
                'children.children.children',
                'head',
                'activeStaffAssignments',
                'children.head',
                'children.activeStaffAssignments',
                'children.children.head',
                'children.children.activeStaffAssignments',
            ])
            ->orderBy('name')
            ->get();

        $heads = User::where('is_active', true)->orderBy('name')->get();

        $allDepartments = Department::orderBy('name')->get();

        return view('livewire.admin.department-manager', [
            'departments' => $departments,
            'heads' => $heads,
            'allDepartments' => $allDepartments,
        ])->layout('layouts.admin');
    }
}
