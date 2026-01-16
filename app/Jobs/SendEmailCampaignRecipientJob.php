<?php

namespace App\Jobs;

use App\Mail\EmailCampaignRecipientMailable;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailUnsubscribe;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailCampaignRecipientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $recipientId)
    {
    }

    public function handle(): void
    {
        /** @var EmailCampaignRecipient|null $recipient */
        $recipient = EmailCampaignRecipient::query()->with('campaign')->find($this->recipientId);
        if (!$recipient) {
            return;
        }

        if (in_array($recipient->status, ['sent', 'skipped'], true)) {
            return;
        }

        $email = trim(strtolower($recipient->email));
        if ($email === '') {
            $recipient->status = 'failed';
            $recipient->error_message = 'Email vacío.';
            $recipient->save();
            return;
        }

        $isUnsubscribed = EmailUnsubscribe::query()->where('email', $email)->exists();
        if ($isUnsubscribed) {
            $recipient->status = 'skipped';
            $recipient->error_message = 'Desuscrito.';
            $recipient->save();
            return;
        }

        try {
            $unsubscribeUrl = url('/email/unsubscribe').'?email='.urlencode($email).'&token='.urlencode(self::unsubscribeToken($email));

            $safeBody = e($recipient->rendered_body);
            $htmlBody = nl2br($safeBody, false);
            $htmlBody .= '<hr style="border:0;border-top:1px solid #e5e7eb;margin:16px 0;">';
            $htmlBody .= '<p style="font-size:12px;color:#6b7280;margin:0;">';
            $htmlBody .= 'Si no deseas recibir estos correos, puedes <a href="'.e($unsubscribeUrl).'">darte de baja aquí</a>.';
            $htmlBody .= '</p>';

            Mail::to($email)->send(new EmailCampaignRecipientMailable($recipient->rendered_subject, $htmlBody));

            $recipient->status = 'sent';
            $recipient->sent_at = Carbon::now();
            $recipient->error_message = null;
            $recipient->save();
        } catch (Throwable $e) {
            $recipient->status = 'failed';
            $recipient->error_message = mb_substr((string) $e->getMessage(), 0, 255);
            $recipient->save();

            throw $e;
        }
    }

    private static function unsubscribeToken(string $email): string
    {
        $secret = (string) config('app.key');
        return hash_hmac('sha256', strtolower(trim($email)), $secret);
    }
}
