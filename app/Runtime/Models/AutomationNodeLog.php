<?php

namespace App\Runtime\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationNodeLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'input_data' => 'json',
        'output_data' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
