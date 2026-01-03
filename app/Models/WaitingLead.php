<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingLead extends Model
{
    use HasFactory;

    protected $table = 'waiting_leads';

    protected $fillable = [
        'lead_id',
        'name',
        'contact_name',
        'contact_phone',
        'contact_email',
        'company_name',
        'company_address',
        'document_type',
        'document_number',
        'observacion',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
