<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailUnsubscribe extends Model
{
    protected $fillable = [
        'email',
        'unsubscribed_at',
        'reason',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];
}
