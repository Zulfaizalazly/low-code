<?php

namespace App\Kernel\Events;

use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    protected $table = 'event_logs';

    protected $guarded = [];

    protected $casts = [
        'event_payload' => 'json',
        'emitted_at' => 'datetime',
    ];

    public $timestamps = false;
}
