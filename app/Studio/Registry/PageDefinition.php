<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageDefinition extends Model
{
    protected $guarded = [];

    public function featureVersion(): BelongsTo
    {
        return $this->belongsTo(FeatureVersion::class);
    }

    protected $casts = [
        'config' => 'json',
        'is_entry_page' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(FormStep::class)->orderBy('sort_order');
    }
}
