<?php

namespace App\Domain\Approval\Models;

use App\Kernel\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;

class ApprovalTask extends Model
{
    use HasAuditTrail;

    protected $guarded = [];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function approvable()
    {
        return $this->morphTo();
    }
}
