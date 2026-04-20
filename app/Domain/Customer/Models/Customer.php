<?php

namespace App\Domain\Customer\Models;

use App\Kernel\Traits\HasAuditTrail;
use App\Kernel\Traits\HasScoping;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasAuditTrail, HasScoping;

    protected $guarded = [];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }
}
