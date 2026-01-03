<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'contact_name',
        'contact_phone',
        'contact_email',
        'company_name',
        'company_address',
        'document_type',
        'document_number',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'customer_id');
    }

    public function incidences(): HasMany
    {
        return $this->hasMany(Incidence::class, 'customer_id');
    }

    public function contadores(): BelongsToMany
    {
        return $this->belongsToMany(Contador::class, 'contador_customer', 'customer_id', 'contador_id')
            ->withPivot(['fecha_asignacion']);
    }
}
