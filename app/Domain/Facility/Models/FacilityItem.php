<?php

namespace App\Domain\Facility\Models;

use App\Kernel\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;

class FacilityItem extends Model
{
    use HasAuditTrail; // Items are auditable

    protected $guarded = [];

    protected $casts = [
        'weight_grams' => 'decimal:4',
        'purity' => 'decimal:2',
        'valuation_amount' => 'decimal:2',
    ];
}
