<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use App\Models\Organization\Entity;
use App\Models\Organization\StaffAssignment;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Kernel\Traits\HasAuditTrail;
use App\Kernel\Traits\HasScoping;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 
    'email', 
    'password', 
    'entity_id', 
    'employee_number',
    'phone',
    'avatar',
    'is_active',
    'joined_at',
    'left_at'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasAuditTrail, HasScoping, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'joined_at' => 'date',
            'left_at' => 'date',
        ];
    }

    /**
     * Get the entity that owns this user
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Get all staff assignments for this user
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }

    /**
     * Get the primary assignment
     */
    public function primaryAssignment(): HasOne
    {
        return $this->hasOne(StaffAssignment::class)
            ->where('is_primary', true)
            ->whereNull('ended_at');
    }

    /**
     * Get active assignments
     */
    public function activeAssignments(): HasMany
    {
        return $this->assignments()
            ->whereNull('ended_at')
            ->where('started_at', '<=', now());
    }

    /**
     * Get the primary branch
     */
    public function getPrimaryBranch(): ?Branch
    {
        $assignment = $this->primaryAssignment;
        return $assignment?->branch;
    }

    /**
     * Get the primary department
     */
    public function getPrimaryDepartment(): ?Department
    {
        $assignment = $this->primaryAssignment;
        return $assignment?->department;
    }

    /**
     * Check if user is HQ staff
     */
    public function isHQStaff(): bool
    {
        return $this->primaryAssignment?->department_id !== null;
    }

    /**
     * Check if user is branch staff
     */
    public function isBranchStaff(): bool
    {
        return $this->primaryAssignment?->branch_id !== null;
    }

    /**
     * Check if user can access a specific branch
     */
    public function canAccessBranch(int $branchId): bool
    {
        // Super admin can access all
        if ($this->hasRole('super-admin')) {
            return true;
        }

        // HQ admin can access all branches in entity
        if ($this->hasRole('hq-admin')) {
            return Branch::where('id', $branchId)
                ->where('entity_id', $this->entity_id)
                ->exists();
        }

        // Branch manager can access own branch
        if ($this->hasRole('branch-manager')) {
            $primaryBranch = $this->getPrimaryBranch();
            return $primaryBranch && $primaryBranch->id === $branchId;
        }

        // Branch staff can only access own branch
        if ($this->isBranchStaff()) {
            $primaryBranch = $this->getPrimaryBranch();
            return $primaryBranch && $primaryBranch->id === $branchId;
        }

        return false;
    }

    /**
     * Get all branches user can access
     */
    public function getAccessibleBranches()
    {
        if ($this->hasRole('super-admin')) {
            return Branch::all();
        }

        if ($this->hasRole('hq-admin')) {
            return Branch::where('entity_id', $this->entity_id)->get();
        }

        if ($this->hasRole('branch-manager') || $this->isBranchStaff()) {
            $primaryBranch = $this->getPrimaryBranch();
            return $primaryBranch ? collect([$primaryBranch]) : collect();
        }

        return collect();
    }

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
