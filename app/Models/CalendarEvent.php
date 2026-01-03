<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'location',
        'all_day',
        'start_at',
        'end_at',
        'reminder_minutes',
        'reminder_at',
        'reminded_at',
        'related_type',
        'related_id',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'all_day' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'reminder_at' => 'datetime',
        'reminded_at' => 'datetime',
        'reminder_minutes' => 'integer',
    ];

    public function related(): MorphTo
    {
        return $this->morphTo('related');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
