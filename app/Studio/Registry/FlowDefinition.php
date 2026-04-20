<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlowDefinition extends Model
{
    protected $guarded = [];

    protected $casts = [
        'trigger_config' => 'json',
        'is_primary' => 'boolean',
    ];

    public function nodes(): HasMany
    {
        return $this->hasMany(FlowNode::class);
    }

    public function edges(): HasMany
    {
        return $this->hasMany(FlowEdge::class);
    }
}
