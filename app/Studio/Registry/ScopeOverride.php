<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;

class ScopeOverride extends Model
{
    protected $table = 'feature_scope_overrides';
    
    protected $guarded = [];

    protected $casts = [
        'override_config' => 'json',
        'is_active' => 'boolean',
    ];
}
