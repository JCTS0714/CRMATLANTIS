<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contador extends Model
{
    protected $table = 'contadores';

    protected $fillable = [
        'nro',
        'comercio',
        'nom_contador',
        'titular_tlf',
        'telefono',
        'telefono_actu',
        'link',
        'usuario',
        'contrasena',
        'servidor',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'contador_customer', 'contador_id', 'customer_id')
            ->withPivot(['fecha_asignacion']);
    }
}
