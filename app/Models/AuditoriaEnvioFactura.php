<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaEnvioFactura extends Model
{
    protected $table = 'auditoria_envios_factura';

    public $timestamps = false;

    protected $fillable = [
        'accion',
        'pago_id',
        'cliente_id',
        'usuario_id',
        'detalles',
        'created_at',
    ];

    protected $casts = [
        'detalles' => 'array',
        'created_at' => 'datetime',
    ];
}
