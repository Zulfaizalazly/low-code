<?php

namespace App\Models\Branch;

use App\Models\User;
use App\Studio\Registry\Feature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FeatureAccessLog extends Model
{
    protected $fillable = [
        'user_id',
        'feature_id',
        'feature_version_id',
        'page_definition_id',
        'branch_id',
        'access_type',
        'session_duration_seconds',
        'accessed_at',
        'completed_at',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ─── Relationships ───

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    // ─── Scopes ───

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('accessed_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('accessed_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeRecent(Builder $query, int $minutes = 15): Builder
    {
        return $query->where('accessed_at', '>=', now()->subMinutes($minutes));
    }
}
