<?php

namespace App\Kernel\Traits;

trait HasVersioning
{
    public function scopeVersion($query, $versionNo)
    {
        return $query->where('version_no', $versionNo);
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderBy('version_no', 'desc');
    }
}
