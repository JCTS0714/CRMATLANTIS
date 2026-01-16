<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCampaignRecipientMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $subjectText,
        public readonly string $htmlBody
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->subjectText)
            ->html($this->htmlBody);
    }
}
