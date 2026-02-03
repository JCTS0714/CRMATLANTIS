<?php

namespace App\Repositories\Lead;

use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LeadRepositoryInterface
{
    /**
     * Find leads with filters and search
     *
     * @param array{stageIds?: array<int>, search?: string, dateFrom?: string, dateTo?: string} $filters
     */
    public function findWithFilters(array $filters = []): Collection;

    /**
     * Get paginated leads with filters
     *
     * @param array{stageId?: int, search?: string} $filters
     */
    public function findForTable(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get leads for board/kanban view
     *
     * @param array{stageIds: array<int>, dateFrom?: string, dateTo?: string} $filters
     */
    public function findForBoard(array $filters, int $limit = 20): Collection;

    /**
     * Count leads grouped by stage
     *
     * @param array{stageIds: array<int>, search?: string, dateFrom?: string, dateTo?: string} $filters
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function countByStage(array $filters): \Illuminate\Support\Collection;

    /**
     * Find lead by ID with relations
     */
    public function find(int $id, array $with = []): ?Lead;

    /**
     * Create a new lead
     */
    public function create(array $data): Lead;

    /**
     * Update a lead
     */
    public function update(Lead $lead, array $data): bool;

    /**
     * Check if lead exists with given document
     */
    public function existsByDocument(string $documentType, string $documentNumber, ?int $excludeId = null): bool;

    /**
     * Find lead by document
     */
    public function findByDocument(string $documentType, string $documentNumber): ?Lead;

    /**
     * Get all active leads (not archived)
     */
    public function getActive(): Collection;

    /**
     * Get archived leads
     */
    public function getArchived(): Collection;
}
