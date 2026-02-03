<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppCampaign extends Model
{
    protected $table = 'whatsapp_campaigns';

    protected $fillable = [
        'created_by',
        'name',
        'message_template',
        'status',
    ];

    // ==================== RELATIONSHIPS ====================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsAppCampaignRecipient::class, 'campaign_id');
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
