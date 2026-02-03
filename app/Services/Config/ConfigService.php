<?php

namespace App\Services\Config;

use App\Models\LeadStage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ConfigService
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Get all lead stages (cached)
     * 
     * @return Collection<LeadStage>
     */
    public function getLeadStages(): Collection
    {
        return Cache::remember('config.lead_stages', self::CACHE_TTL, function () {
            return LeadStage::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * Get active lead stages (not won)
     * 
     * @return Collection<LeadStage>
     */
    public function getActiveLeadStages(): Collection
    {
        return Cache::remember('config.active_lead_stages', self::CACHE_TTL, function () {
            return LeadStage::query()
                ->where('is_won', false)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * Get a single lead stage by ID (cached)
     */
    public function getLeadStageById(int $id): ?LeadStage
    {
        $stages = $this->getLeadStages();
        return $stages->firstWhere('id', $id);
    }

    /**
     * Get stages indexed by ID for quick lookup
     * 
     * @return Collection<int, LeadStage>
     */
    public function getLeadStagesById(): Collection
    {
        return $this->getLeadStages()->keyBy('id');
    }

    /**
     * Check if a stage is marked as "won"
     */
    public function isWonStage(int $stageId): bool
    {
        $stage = $this->getLeadStageById($stageId);
        return $stage ? (bool) $stage->is_won : false;
    }

    /**
     * Invalidate all lead stages cache
     */
    public function invalidateLeadStagesCache(): void
    {
        Cache::forget('config.lead_stages');
        Cache::forget('config.active_lead_stages');
    }

    /**
     * Invalidate all configuration cache
     */
    public function invalidateAllCache(): void
    {
        $this->invalidateLeadStagesCache();
        // Add more cache invalidations here as we add more cached configs
    }
}
