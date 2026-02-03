<?php

namespace App\DTOs\Campaign;

abstract class BaseCampaignResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $subject,
        public readonly string $status,
        public readonly ?string $source,
        public readonly ?string $scheduledAt,
        public readonly ?string $sentAt,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    /**
     * Convert to array for JSON response
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subject' => $this->subject,
            'status' => $this->status,
            'source' => $this->source,
            'scheduled_at' => $this->scheduledAt,
            'sent_at' => $this->sentAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Compact version for mobile
     */
    public function toCompactArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'scheduled_at' => $this->scheduledAt,
        ];
    }
}
