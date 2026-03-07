<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrumTask extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'asignador_id',
        'responsable_id',
        'prioridad',
        'tiempo_ejecucion',
        'observacion',
        'estado',
    ];

    protected $casts = [
        'tiempo_ejecucion' => 'datetime',
    ];

    public function asignador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignador_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
