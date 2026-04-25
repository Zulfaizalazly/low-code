<?php

namespace App\Livewire\Admin;

use App\Models\Organization\Entity;
use Livewire\Component;

class EntitySettings extends Component
{
    public ?Entity $entity = null;
    public string $entityName = '';
    public string $entityCode = '';
    public string $registrationNumber = '';
    public string $licenseNumber = '';
    public string $address = '';
    public string $phone = '';
    public string $email = '';
    public array $settings = [];
    public string $newSettingKey = '';
    public string $newSettingValue = '';
    public bool $showCreateForm = false;

    public function mount()
    {
        $this->entity = Entity::first();

        if ($this->entity) {
            $this->fillFromEntity();
        } else {
            $this->showCreateForm = true;
        }
    }

    protected function fillFromEntity(): void
    {
        $this->entityName = $this->entity->name ?? '';
        $this->entityCode = $this->entity->code ?? '';
        $this->registrationNumber = $this->entity->registration_number ?? '';
        $this->licenseNumber = $this->entity->license_number ?? '';
        $this->address = $this->entity->address ?? '';
        $this->phone = $this->entity->phone ?? '';
        $this->email = $this->entity->email ?? '';
        $this->settings = $this->entity->settings ?? [];
    }

    public function save()
    {
        $rules = [
            'entityName' => 'required|string|max:255',
            'entityCode' => 'required|string|max:50',
            'registrationNumber' => 'nullable|string|max:255',
            'licenseNumber' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->entityName,
            'code' => $this->entityCode,
            'registration_number' => $this->registrationNumber,
            'license_number' => $this->licenseNumber,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
        ];

        if ($this->entity) {
            $this->entity->update($data);
        } else {
            $this->entity = Entity::create(array_merge($data, [
                'is_active' => true,
                'settings' => [],
            ]));
            $this->showCreateForm = false;
        }

        session()->flash('success', 'Entity details saved successfully.');
    }

    public function saveSettings()
    {
        if (!$this->entity) {
            return;
        }

        $this->entity->update(['settings' => $this->settings]);
        session()->flash('success', 'Entity settings saved successfully.');
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
        return view('livewire.admin.entity-settings')
            ->layout('layouts.admin');
    }
}
