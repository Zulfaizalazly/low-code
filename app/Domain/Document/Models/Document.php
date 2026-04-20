<?php

namespace App\Domain\Document\Models;

use App\Kernel\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasAuditTrail;

    protected $guarded = [];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }
}
