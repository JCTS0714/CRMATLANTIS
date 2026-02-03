<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStage extends Model
{
    protected $fillable = [
        'key',
        'name',
        'sort_order',
        'is_won',
    ];

    protected $casts = [
        'is_won' => 'boolean',
    ];

    /**
     * Boot method to invalidate cache on model changes
     */
    protected static function booted(): void
    {
        static::saved(function () {
            app(\App\Services\Config\ConfigService::class)->invalidateLeadStagesCache();
        });

        static::deleted(function () {
            app(\App\Services\Config\ConfigService::class)->invalidateLeadStagesCache();
        });
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'stage_id');
    }
}
