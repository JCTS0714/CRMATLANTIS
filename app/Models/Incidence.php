<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incidence extends Model
{
    protected $fillable = [
        'correlative',
        'stage_id',
        'sort_order',
        'customer_id',
        'created_by',
        'title',
        'date',
        'priority',
        'notes',
        'archived_at',
    ];

    protected $casts = [
        'date' => 'date',
        'archived_at' => 'datetime',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(IncidenceStage::class, 'stage_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
