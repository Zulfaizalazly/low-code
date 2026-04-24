<?php

namespace App\Models\Branch;

use App\Models\User;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ChangeDeployment extends Model
{
    protected $fillable = [
        'feature_id',
        'feature_version_id',
        'deployed_by',
        'deployed_at',
        'change_summary',
        'is_visible_to_branches',
        'notified_at',
    ];

    protected $casts = [
        'deployed_at' => 'datetime',
        'notified_at' => 'datetime',
        'is_visible_to_branches' => 'boolean',
    ];

    // ─── Relationships ───

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function featureVersion(): BelongsTo
    {
        return $this->belongsTo(FeatureVersion::class);
    }

    public function deployedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deployed_by');
    }

    // ─── Scopes ───

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('deployed_at', '>=', now()->subDays($days));
    }

    public function scopeVisibleToBranches(Builder $query): Builder
    {
        return $query->where('is_visible_to_branches', true);
    }

    // ─── Helpers ───

    public function isNew(): bool
    {
        $hours = config('branch.dashboard.new_feature_badge_hours', 24);
        return $this->deployed_at->diffInHours(now()) < $hours;
    }
}
