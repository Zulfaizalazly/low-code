<?php

namespace App\Livewire\Admin;

use App\Models\Organization\Branch;
use Livewire\Component;

class BranchDetail extends Component
{
    public Branch $branch;
    public string $activeTab = 'overview';
    public array $settings = [];
    public string $newSettingKey = '';
    public string $newSettingValue = '';

    public function mount(Branch $branch)
    {
        $this->branch = $branch->load(['region', 'manager', 'entity', 'activeStaffAssignments.user']);
        $this->settings = $branch->settings ?? [];
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function saveSettings()
    {
        $this->branch->update(['settings' => $this->settings]);
        session()->flash('success', 'Branch settings saved successfully.');
    }

    public function addSetting()
    {
        $this->validate([
            'newSettingKey' => 'required|string|max:255',
            'newSettingValue' => 'nullable|string|max:255',
        ]);

        if (array_key_exists($this->newSettingKey, $this->settings)) {
            $this->addError('newSettingKey', 'This setting key already exists.');
            return;
        }

        $this->settings[$this->newSettingKey] = $this->newSettingValue;
        $this->newSettingKey = '';
        $this->newSettingValue = '';
        $this->resetErrorBag();
    }

    public function removeSetting(string $key)
    {
        unset($this->settings[$key]);
    }

    public function render()
    {
        return view('livewire.admin.branch-detail')
            ->layout('layouts.admin');
    }
}
