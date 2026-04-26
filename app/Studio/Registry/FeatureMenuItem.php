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

    /**
     * Ensure route_key always has a leading slash for absolute URL resolution.
     */
    public function getRouteKeyAttribute(string $value): string
    {
        return str_starts_with($value, '/') ? $value : '/' . $value;
    }
}
