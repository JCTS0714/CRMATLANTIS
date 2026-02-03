<?php

namespace App\DTOs\Lead;

use App\DTOs\Shared\PaginationDto;
use Illuminate\Support\Collection;

class LeadCollectionResponseDto
{
    /**
     * @param Collection<StageResponseDto> $stages
     * @param Collection<LeadResponseDto> $leads
     */
    public function __construct(
        public readonly Collection $stages,
        public readonly Collection $leads,
        public readonly int $totalCount,
        public readonly ?PaginationDto $pagination = null,
        public readonly array $filters = [],
    ) {}

    /**
     * Convert to array for JSON response
     */
    public function toArray(): array
    {
        $data = [
            'stages' => $this->stages->map(fn($stage) => $stage->toArray())->values(),
            'total_count' => $this->totalCount,
            'leads' => $this->leads->map(fn($lead) => $lead->toArray())->values(),
        ];

        if ($this->pagination) {
            $data['pagination'] = $this->pagination->toArray();
        }

        if (!empty($this->filters)) {
            $data['filters'] = $this->filters;
        }

        return $data;
    }

    /**
     * Compact version for mobile (simpler structure)
     */
    public function toCompactArray(): array
    {
        return [
            'stages' => $this->stages->map(fn($stage) => $stage->toArray())->values(),
            'leads' => $this->leads->map(fn($lead) => $lead->toCompactArray())->values(),
            'total' => $this->totalCount,
        ];
    }
}
