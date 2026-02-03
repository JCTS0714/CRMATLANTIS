<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $fillable = [
        'created_by',
        'name',
        'source',
        'subject_template',
        'body_template',
        'status',
    ];

    // ==================== RELATIONSHIPS ====================

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope to filter by status
     *
     * @param  Builder  $query
     * @param  string  $status
     * @return Builder
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by source (leads or customers)
     *
     * @param  Builder  $query
     * @param  string  $source
     * @return Builder
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope to get draft campaigns
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get sent campaigns
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope to eager load recipients with counts
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWithRecipients(Builder $query): Builder
    {
        return $query->withCount('recipients');
    }
}
