<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificado extends Model
{
    protected $table = 'certificados';

    protected $fillable = [
        'nombre',
        'ruc',
        'usuario',
        'clave',
        'fecha_creacion',
        'fecha_vencimiento',
        'estado',
        'tipo',
        'observacion',
        'ultima_notificacion',
        'imagen',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'fecha_vencimiento' => 'date',
        'ultima_notificacion' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
