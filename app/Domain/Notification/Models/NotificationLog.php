<?php

namespace App\Domain\Notification\Models;

use App\Kernel\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasAuditTrail;

    protected $table = 'notification_logs';

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
