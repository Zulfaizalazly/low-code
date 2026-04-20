<?php

namespace App\Domain\Valuation\Models;

use App\Kernel\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;

class Valuation extends Model
{
    use HasAuditTrail;

    protected $guarded = [];

    protected $casts = [
        'gold_price_per_gram' => 'decimal:2',
        'weight_grams' => 'decimal:4',
        'purity_percentage' => 'decimal:2',
        'gross_value' => 'decimal:2',
        'ltv_percentage' => 'decimal:2',
        'valuation_amount' => 'decimal:2',
        'valued_at' => 'datetime',
    ];
}
