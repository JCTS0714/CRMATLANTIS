<?php

namespace App\DTOs\Campaign;

use App\Models\WhatsAppCampaign;

class WhatsAppCampaignResponseDto extends BaseCampaignResponseDto
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
        public readonly ?string $message,
        public readonly ?int $recipientsCount,
    ) {
        parent::__construct($id, $name, $subject, $status, $source, $scheduledAt, $sentAt, $createdAt, $updatedAt);
    }

    /**
     * Create DTO from WhatsAppCampaign model
     */
    public static function fromModel(WhatsAppCampaign $campaign, bool $includeMessage = true): self
    {
        return new self(
            id: $campaign->id,
            name: $campaign->name ?? '',
            subject: null, // WhatsApp campaigns don't have subjects
            status: $campaign->status ?? 'draft',
            source: $campaign->source ?? 'leads',
            scheduledAt: null, // WhatsApp campaigns don't have scheduling yet
            sentAt: null, // WhatsApp campaigns don't have sent_at yet
            createdAt: $campaign->created_at->format('Y-m-d H:i:s'),
            updatedAt: $campaign->updated_at->format('Y-m-d H:i:s'),
            message: $includeMessage ? $campaign->message_template : null,
            recipientsCount: $campaign->relationLoaded('recipients') ? $campaign->recipients->count() : null,
        );
    }

    /**
     * Convert to array for JSON response
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'message' => $this->message,
            'recipients_count' => $this->recipientsCount,
        ]);
    }

    /**
     * Compact version for mobile (excludes message)
     */
    public function toCompactArray(): array
    {
        return array_merge(parent::toCompactArray(), [
            'recipients_count' => $this->recipientsCount,
        ]);
    }
}
