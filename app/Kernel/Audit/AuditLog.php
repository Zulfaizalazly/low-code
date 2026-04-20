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
        'performed_at' => 'datetime',
    ];

    public $timestamps = false;

    public static function record(string $action, Model $subject, ?array $old, ?array $new): void
    {
        static::create([
            'auditable_type' => get_class($subject),
            'auditable_id' => $subject->getKey(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }
}
