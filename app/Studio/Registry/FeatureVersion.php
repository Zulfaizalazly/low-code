<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureVersion extends Model
{
    protected $guarded = [];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function flows(): HasMany
    {
        return $this->hasMany(FlowDefinition::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(PageDefinition::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(FeatureMenuItem::class);
    }
}
