<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WhatsAppCampaignRecipient extends Model
{
    protected $table = 'whatsapp_campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'contactable_type',
        'contactable_id',
        'display_name',
        'phone',
        'rendered_message',
        'status',
        'opened_at',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsAppCampaign::class, 'campaign_id');
    }

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }
}
