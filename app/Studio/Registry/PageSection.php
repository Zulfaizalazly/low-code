<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $guarded = [];

    protected $casts = [
        'config' => 'json',
    ];
}
