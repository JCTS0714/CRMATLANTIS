<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'contact_name',
        'contact_phone',
        'contact_email',
        'company_name',
        'company_address',
        'document_type',
        'document_number',
    ];

    // ==================== RELATIONSHIPS ====================

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'customer_id');
    }

    public function incidences(): HasMany
    {
        return $this->hasMany(Incidence::class, 'customer_id');
    }

    public function contadores(): BelongsToMany
    {
        return $this->belongsToMany(Contador::class, 'contador_customer', 'customer_id', 'contador_id')
            ->withPivot(['fecha_asignacion']);
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope to search customers by multiple fields
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
     * Scope to filter by document
     *
     * @param  Builder  $query
     * @param  string  $documentType
     * @param  string  $documentNumber
     * @return Builder
     */
    public function scopeByDocument(Builder $query, string $documentType, string $documentNumber): Builder
    {
        return $query->where('document_type', $documentType)
            ->where('document_number', $documentNumber);
    }

    /**
     * Scope to eager load common relationships
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with(['leads', 'incidences']);
    }
}
