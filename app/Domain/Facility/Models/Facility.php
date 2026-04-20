<?php

namespace App\Domain\Facility\Models;

use App\Kernel\Traits\HasAuditTrail;
use App\Kernel\Traits\HasScoping;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasAuditTrail, HasScoping;

    protected $guarded = [];

    protected $casts = [
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'matured_at' => 'datetime',
        'principal_amount' => 'decimal:2',
        'profit_rate' => 'decimal:4',
    ];

    public function items()
    {
        return $this->hasMany(FacilityItem::class);
    }

    public function nominees()
    {
        return $this->hasMany(FacilityNominee::class);
    }
}
