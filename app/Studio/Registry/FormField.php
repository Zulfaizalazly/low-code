<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormField extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_required' => 'boolean',
        'config' => 'json',
        'default_value' => 'json',
    ];

    public function binding(): HasOne
    {
        return $this->hasOne(FieldBinding::class);
    }
}
