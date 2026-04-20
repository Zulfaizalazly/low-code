<?php

namespace App\Studio\Registry;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Feature extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function versions(): HasMany
    {
        return $this->hasMany(FeatureVersion::class);
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(FeatureVersion::class)->where('status', 'published')->latestOfMany();
    }
}
