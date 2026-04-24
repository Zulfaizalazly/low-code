<?php

namespace App\Models\Organization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'registration_number',
        'license_number',
        'address',
        'city',
        'state',
        'postcode',
        'phone',
        'email',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get all regions for this entity
     */
    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    /**
     * Get all branches for this entity
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get all departments for this entity
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get all staff assignments for this entity
     */
    public function staffAssignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }

    /**
     * Get the headquarters branch
     */
    public function headquarters()
    {
        return $this->branches()->where('type', 'hq')->first();
    }

    /**
     * Get active branches
     */
    public function activeBranches(): HasMany
    {
        return $this->branches()->where('is_active', true);
    }

    /**
     * Get active departments
     */
    public function activeDepartments(): HasMany
    {
        return $this->departments()->where('is_active', true);
    }

    /**
     * Scope to get only active entities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
