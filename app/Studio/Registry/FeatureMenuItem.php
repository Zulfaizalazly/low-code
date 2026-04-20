<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;

class FeatureMenuItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_enabled' => 'boolean',
        'config' => 'json',
    ];
}
