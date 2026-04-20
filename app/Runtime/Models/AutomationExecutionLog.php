<?php

namespace App\Runtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationExecutionLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function nodeLogs(): HasMany
    {
        return $this->hasMany(AutomationNodeLog::class, 'execution_log_id');
    }
}
