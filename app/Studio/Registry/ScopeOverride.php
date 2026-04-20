<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScopeOverride extends Model
{
    protected $table = 'scoped_overrides';
    
    protected $guarded = [];

    protected $casts = [
        'override_value' => 'json',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Get the feature version that owns the scope override.
     */
    public function featureVersion(): BelongsTo
    {
        return $this->belongsTo(FeatureVersion::class);
    }

    /**
     * Check if the override is currently active.
     */
    public function isActive(): bool
    {
        $now = now()->toDateString();
        
        if ($this->effective_from > $now) {
            return false;
        }

        if ($this->effective_to && $this->effective_to < $now) {
            return false;
        }

        return true;
    }

    /**
     * Scope query to only active overrides.
     */
    public function scopeActive($query)
    {
        $now = now()->toDateString();
        
        return $query->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $now);
            });
    }

    /**
     * Scope query by scope type and ID.
     */
    public function scopeForScope($query, string $scopeType, string $scopeId)
    {
        return $query->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId);
    }

    /**
     * Scope query by target.
     */
    public function scopeForTarget($query, string $targetTable, string $targetKey)
    {
        return $query->where('target_table', $targetTable)
            ->where('target_key', $targetKey);
    }
}
