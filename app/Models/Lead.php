<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'stage_id',
        'customer_id',
        'created_by',
        'name',
        'amount',
        'currency',
        'contact_name',
        'contact_phone',
        'contact_email',
        'company_name',
        'company_address',
        'document_type',
        'document_number',
        'observacion',
        'migracion',
        'won_at',
        'archived_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'won_at' => 'datetime',
        'migracion' => 'date',
        'archived_at' => 'datetime',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
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
