<?php

namespace App\Kernel\Traits;

use App\Kernel\Audit\AuditLog;

trait HasAuditTrail
{
    public static function bootHasAuditTrail(): void
    {
        static::created(function ($model) {
            AuditLog::record(
                action: 'created',
                subject: $model,
                old: null,
                new: $model->getAttributes()
            );
        });

        static::updated(function ($model) {
            AuditLog::record(
                action: 'updated',
                subject: $model,
                old: $model->getOriginal(),
                new: $model->getAttributes()
            );
        });

        static::deleted(function ($model) {
            AuditLog::record(
                action: 'deleted',
                subject: $model,
                old: $model->getOriginal(),
                new: null
            );
        });
    }
}
