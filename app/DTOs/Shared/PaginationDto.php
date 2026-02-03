<?php

namespace App\DTOs\Shared;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginationDto
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $lastPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly ?int $from,
        public readonly ?int $to,
    ) {}

    /**
     * Create from Laravel paginator
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
            from: $paginator->firstItem(),
            to: $paginator->lastItem(),
        );
    }

    /**
     * Convert to array for JSON response
     */
    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
