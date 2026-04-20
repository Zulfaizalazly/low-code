<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormStep extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_required' => 'boolean',
        'config' => 'json',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }
}
