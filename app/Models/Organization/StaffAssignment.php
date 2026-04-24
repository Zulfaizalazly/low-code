<?php

namespace App\Models\Organization;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'entity_id',
        'branch_id',
        'department_id',
        'position',
        'employment_type',
        'started_at',
        'ended_at',
        'is_primary',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the user for this assignment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the entity for this assignment
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Get the branch for this assignment
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the department for this assignment
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Scope to get active assignments
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at')
            ->where('started_at', '<=', now());
    }

    /**
     * Scope to get primary assignments
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to get branch assignments
     */
    public function scopeBranchAssignments($query)
    {
        return $query->whereNotNull('branch_id');
    }

    /**
     * Scope to get department assignments
     */
    public function scopeDepartmentAssignments($query)
    {
        return $query->whereNotNull('department_id');
    }

    /**
     * Check if assignment is active
     */
    public function isActive(): bool
    {
        return is_null($this->ended_at) && $this->started_at <= now();
    }

    /**
     * Check if this is a branch assignment
     */
    public function isBranchAssignment(): bool
    {
        return !is_null($this->branch_id);
    }

    /**
     * Check if this is a department assignment
     */
    public function isDepartmentAssignment(): bool
    {
        return !is_null($this->department_id);
    }

    /**
     * Get location name (branch or department)
     */
    public function getLocationNameAttribute()
    {
        if ($this->branch) {
            return $this->branch->name;
        }

        if ($this->department) {
            return $this->department->name;
        }

        return 'N/A';
    }

    /**
     * Get location type
     */
    public function getLocationTypeAttribute()
    {
        if ($this->branch) {
            return 'Branch';
        }

        if ($this->department) {
            return 'Department';
        }

        return 'N/A';
    }
}
