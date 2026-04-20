<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_primary' => 'boolean',
    ];
}
