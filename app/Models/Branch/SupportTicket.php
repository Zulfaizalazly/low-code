<?php

namespace App\Models\Branch;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'context_json',
        'it_responder_id',
        'response_note',
        'responded_at',
        'resolved_at',
    ];

    protected $casts = [
        'context_json' => 'array',
        'responded_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // ─── Relationships ───

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'it_responder_id');
    }

    // ─── Scopes ───

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ─── Helpers ───

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'rose',
            'high' => 'orange',
            'medium' => 'amber',
            'low' => 'blue',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'amber',
            'in_progress' => 'blue',
            'resolved' => 'emerald',
            'closed' => 'gray',
            default => 'gray',
        };
    }
}
