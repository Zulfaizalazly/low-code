<?php

namespace App\Domain\Payment\Models;

use App\Kernel\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasAuditTrail;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
}
