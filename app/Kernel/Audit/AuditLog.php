<?php

namespace App\Kernel\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    protected $table = 'audit_trails';

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'payload' => 'json',
        'performed_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Unified audit record method.
     * Supports both Studio operations (polymorphic subject with old/new values)
     * and Branch operations (branch_id, description, payload).
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        ?array $old = null,
        ?array $new = null,
        ?int $branchId = null,
        ?string $description = null,
        ?array $payload = null
    ): void {
        static::create([
            'auditable_type' => $subject ? get_class($subject) : null,
            'auditable_id' => $subject?->getKey(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'user_id' => Auth::id(),
            'branch_id' => $branchId,
            'description' => $description,
            'payload' => $payload,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
