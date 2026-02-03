<?php

namespace App\Repositories\Lead;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentLeadRepository implements LeadRepositoryInterface
{
    /**
     * Apply base query filters
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        // Filter by stage IDs
        if (!empty($filters['stageIds'])) {
            $query->whereIn('stage_id', $filters['stageIds']);
        }

        // Filter by specific stage ID
        if (isset($filters['stageId'])) {
            $query->where('stage_id', $filters['stageId']);
        }

        // Search filter using scope
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Date range filter using scope
        if (!empty($filters['dateFrom'])) {
            $query->dateRange(
                $filters['dateFrom'],
                $filters['dateTo'] ?? null
            );
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function findWithFilters(array $filters = []): Collection
    {
        $query = Lead::query();

        $this->applyFilters($query, $filters);

        // Use active() scope unless explicitly including archived
        if (!isset($filters['includeArchived']) || !$filters['includeArchived']) {
            $query->active();
        }

        return $query->withRelations()
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findForTable(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Lead::query();

        $this->applyFilters($query, $filters);

        // Use active() scope
        $query->active();

        return $query->withRelations()
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function findForBoard(array $filters, int $limit = 20): Collection
    {
        $query = Lead::query();

        $this->applyFilters($query, $filters);

        // Use active() and byPosition() scopes
        $query->active()->byPosition();

        return $query->limit($limit)
            ->get([
                'id',
                'stage_id',
                'customer_id',
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
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function countByStage(array $filters): \Illuminate\Support\Collection
    {
        $query = Lead::query();

        $this->applyFilters($query, $filters);

        // Use active() scope unless explicitly including archived
        if (!isset($filters['includeArchived']) || !$filters['includeArchived']) {
            $query->active();
        }

        return $query->select('stage_id', DB::raw('count(*) as count'))
            ->groupBy('stage_id')
            ->pluck('count', 'stage_id');
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id, array $with = []): ?Lead
    {
        $query = Lead::query();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Lead
    {
        return Lead::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Lead $lead, array $data): bool
    {
        return $lead->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function existsByDocument(string $documentType, string $documentNumber, ?int $excludeId = null): bool
    {
        $query = Lead::query()
            ->where('document_type', $documentType)
            ->where('document_number', $documentNumber);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function findByDocument(string $documentType, string $documentNumber): ?Lead
    {
        return Lead::query()
            ->where('document_type', $documentType)
            ->where('document_number', $documentNumber)
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getActive(): Collection
    {
        return Lead::active()
            ->withRelations()
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getArchived(): Collection
    {
        return Lead::archived()
            ->withRelations()
            ->orderByDesc('archived_at')
            ->get();
    }
}
