<?php

namespace App\Models\Branch;

use App\Studio\Registry\Feature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FeatureHealthCheck extends Model
{
    protected $fillable = [
        'feature_id',
        'status',
        'error_message',
        'checked_at',
        'resolved_at',
        'resolution_note',
        'checked_by',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // ─── Relationships ───

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    // ─── Scopes ───

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeHasIssues(Builder $query): Builder
    {
        return $query->whereIn('status', ['degraded', 'unavailable'])->whereNull('resolved_at');
    }

    public function scopeForFeature(Builder $query, int $featureId): Builder
    {
        return $query->where('feature_id', $featureId);
    }

    // ─── Helpers ───

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}
