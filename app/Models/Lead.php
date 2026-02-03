<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'stage_id',
        'customer_id',
        'created_by',
        'name',
        'amount',
        'currency',
        'contact_name',
        'contact_phone',
        'contact_email',
        'company_name',
        'company_address',
        'document_type',
        'document_number',
        'observacion',
        'migracion',
        'won_at',
        'archived_at',
        'position',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'won_at' => 'datetime',
        'migracion' => 'date',
        'archived_at' => 'datetime',
        'position' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope to search leads by multiple fields
     *
     * @param  Builder  $query
     * @param  string  $search
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = trim($search);
        if ($search === '') {
            return $query;
        }

        $like = '%' . $search . '%';

        return $query->where(function ($q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('company_name', 'like', $like)
                ->orWhere('contact_name', 'like', $like)
                ->orWhere('contact_email', 'like', $like)
                ->orWhere('contact_phone', 'like', $like)
                ->orWhere('document_number', 'like', $like);
        });
    }

    /**
     * Scope to filter leads by stage
     *
     * @param  Builder  $query
     * @param  int|array  $stageId
     * @return Builder
     */
    public function scopeByStage(Builder $query, int|array $stageId): Builder
    {
        if (is_array($stageId)) {
            return $query->whereIn('stage_id', $stageId);
        }

        return $query->where('stage_id', $stageId);
    }

    /**
     * Scope to get only active (non-archived) leads
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope to get only archived leads
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Scope to filter by date range (updated_at)
     *
     * @param  Builder  $query
     * @param  string  $from
     * @param  string|null  $to
     * @return Builder
     */
    public function scopeDateRange(Builder $query, string $from, ?string $to = null): Builder
    {
        $query->where('updated_at', '>=', $from);

        if ($to) {
            $query->where('updated_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Scope to eager load common relationships
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with(['stage', 'customer', 'creator']);
    }

    /**
     * Scope to order by board position
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeByPosition(Builder $query): Builder
    {
        return $query->orderByDesc('position')->orderByDesc('updated_at');
    }
}
