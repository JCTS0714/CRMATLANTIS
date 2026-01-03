<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenceStage extends Model
{
    protected $fillable = [
        'key',
        'name',
        'sort_order',
        'is_done',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_done' => 'boolean',
    ];
}
