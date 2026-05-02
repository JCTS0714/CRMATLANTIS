<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PagoMensual extends Model
{
    protected $table = 'pagos_mensuales';

    protected $fillable = [
        'cliente_id',
        'mes',
        'anio',
        'estado',
    ];

    protected $casts = [
        'mes' => 'integer',
        'anio' => 'integer',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'cliente_id');
    }

    public function envioFactura(): HasOne
    {
        return $this->hasOne(EnvioFactura::class, 'pago_id');
    }
}
