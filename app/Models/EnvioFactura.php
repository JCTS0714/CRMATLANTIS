<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvioFactura extends Model
{
    protected $table = 'envios_factura';

    protected $fillable = [
        'pago_id',
        'archivo_url',
        'mensaje',
        'estado',
        'fecha_preparado',
        'fecha_enviado',
        'canal_envio',
        'message_id',
    ];

    protected $casts = [
        'fecha_preparado' => 'datetime',
        'fecha_enviado' => 'datetime',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(PagoMensual::class, 'pago_id');
    }
}
