<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaMensajePlantilla extends Model
{
    protected $table = 'factura_mensaje_plantillas';

    protected $fillable = [
        'nombre',
        'contenido',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'created_by' => 'integer',
    ];
}
