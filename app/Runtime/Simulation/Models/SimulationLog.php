<?php

namespace App\Runtime\Simulation\Models;

use App\Models\User;
use App\Studio\Registry\FeatureVersion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'test_data' => 'array',
        'results' => 'array',
        'executed_at' => 'datetime',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(FeatureVersion::class, 'feature_version_id');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
