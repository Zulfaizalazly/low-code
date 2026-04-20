<?php

namespace App\Domain\Accounting\Models;

use App\Kernel\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    use HasAuditTrail;

    protected $guarded = [];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];
}
