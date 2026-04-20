<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;

class FlowNode extends Model
{
    protected $guarded = [];

    protected $casts = [
        'config' => 'json',
    ];
}
