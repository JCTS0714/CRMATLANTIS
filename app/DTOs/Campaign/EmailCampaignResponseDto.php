<?php

namespace App\DTOs\Campaign;

use App\Models\EmailCampaign;

class EmailCampaignResponseDto extends BaseCampaignResponseDto
{
    public function __construct(
        int $id,
        string $name,
        ?string $subject,
        string $status,
        ?string $source,
        ?string $scheduledAt,
        ?string $sentAt,
        string $createdAt,
        string $updatedAt,
        public readonly ?string $body,
        public readonly ?int $recipientsCount,
    ) {
        parent::__construct($id, $name, $subject, $status, $source, $scheduledAt, $sentAt, $createdAt, $updatedAt);
    }

    /**
     * Create DTO from EmailCampaign model
     */
    public static function fromModel(EmailCampaign $campaign, bool $includeBody = true): self
    {
        return new self(
            id: $campaign->id,
            name: $campaign->name,
            subject: $campaign->subject,
            status: $campaign->status,
            source: $campaign->source,
            scheduledAt: $campaign->scheduled_at?->format('Y-m-d H:i:s'),
            sentAt: $campaign->sent_at?->format('Y-m-d H:i:s'),
            createdAt: $campaign->created_at->format('Y-m-d H:i:s'),
            updatedAt: $campaign->updated_at->format('Y-m-d H:i:s'),
            body: $includeBody ? $campaign->body : null,
            recipientsCount: $campaign->relationLoaded('recipients') ? $campaign->recipients->count() : null,
        );
    }

    /**
     * Convert to array for JSON response
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'body' => $this->body,
            'recipients_count' => $this->recipientsCount,
        ]);
    }

    /**
     * Compact version for mobile (excludes body)
     */
    public function toCompactArray(): array
    {
        return array_merge(parent::toCompactArray(), [
            'recipients_count' => $this->recipientsCount,
        ]);
    }
}
