<?php

namespace App\DTOs\Lead;

use App\Models\LeadStage;

class StageResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $key,
        public readonly string $name,
        public readonly int $sortOrder,
        public readonly bool $isWon,
        public readonly int $count = 0,
    ) {}

    /**
     * Create from LeadStage model
     */
    public static function fromModel(LeadStage $stage, int $count = 0): self
    {
        return new self(
            id: $stage->id,
            key: $stage->key,
            name: $stage->name,
            sortOrder: $stage->sort_order,
            isWon: (bool) $stage->is_won,
            count: $count,
        );
    }

    /**
     * Convert to array for JSON response
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'sort_order' => $this->sortOrder,
            'is_won' => $this->isWon,
            'count' => $this->count,
        ];
    }
}
