<?php

namespace App\Models\Organization;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entity_id',
        'region_id',
        'code',
        'name',
        'type',
        'address',
        'city',
        'state',
        'postcode',
        'phone',
        'email',
        'manager_id',
        'opening_hours',
        'is_active',
        'opened_at',
        'closed_at',
        'settings',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'is_active' => 'boolean',
        'opened_at' => 'date',
        'closed_at' => 'date',
        'settings' => 'array',
    ];

    /**
     * Get the entity that owns this branch
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Get the region this branch belongs to
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the branch manager
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all staff assignments for this branch
     */
    public function staffAssignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }

    /**
     * Get all staff members through assignments
     */
    public function staff(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            StaffAssignment::class,
            'branch_id',
            'id',
            'id',
            'user_id'
        )->whereNull('staff_assignments.ended_at');
    }

    /**
     * Get active staff assignments
     */
    public function activeStaffAssignments(): HasMany
    {
        return $this->staffAssignments()
            ->whereNull('ended_at')
            ->where('started_at', '<=', now());
    }

    /**
     * Scope to get only active branches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get headquarters
     */
    public function scopeHeadquarters($query)
    {
        return $query->where('type', 'hq');
    }

    /**
     * Scope to get regular branches
     */
    public function scopeRegularBranches($query)
    {
        return $query->where('type', 'branch');
    }

    /**
     * Check if this is headquarters
     */
    public function isHeadquarters(): bool
    {
        return $this->type === 'hq';
    }

    /**
     * Get staff count
     */
    public function getStaffCountAttribute()
    {
        return $this->activeStaffAssignments()->count();
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        return collect([
            $this->address,
            $this->postcode . ' ' . $this->city,
            $this->state,
        ])->filter()->implode(', ');
    }
}
