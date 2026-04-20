<?php

namespace App\Domain\Facility\Models;

use App\Kernel\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;

class FacilityNominee extends Model
{
    use HasAuditTrail;

    protected $guarded = [];

    protected $casts = [
        'is_primary' => 'boolean',
    ];
}
