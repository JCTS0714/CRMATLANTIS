<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignRecipient extends Model
{
    protected $fillable = [
        'campaign_id',
        'contactable_type',
        'contactable_id',
        'display_name',
        'email',
        'rendered_subject',
        'rendered_body',
        'status',
        'queued_at',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }
}
