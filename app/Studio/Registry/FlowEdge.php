<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;

class FlowEdge extends Model
{
    protected $guarded = [];

    protected $casts = [
        'condition_config' => 'json',
    ];
}
