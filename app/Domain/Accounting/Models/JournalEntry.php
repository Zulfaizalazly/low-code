<?php

namespace App\Domain\Accounting\Models;

use App\Kernel\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasAuditTrail;

    protected $guarded = [];

    protected $casts = [
        'posted_at' => 'datetime',
        'is_balanced' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}
