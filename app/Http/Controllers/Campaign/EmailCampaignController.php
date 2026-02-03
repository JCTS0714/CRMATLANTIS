<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Requests\Campaign\CreateEmailCampaignRequest;
use App\Http\Requests\Campaign\SendCampaignRequest;
use App\Jobs\SendEmailCampaignRecipientJob;
use App\Models\Customer;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailUnsubscribe;
use App\Models\Lead;
use App\Models\LeadStage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class EmailCampaignController extends BaseCampaignController
{
    protected function getContactField(): string
    {
        return 'email';
    }

    protected function getCampaignModel(): string
    {
        return EmailCampaign::class;
    }

    protected function getRecipientModel(): string
    {
        return EmailCampaignRecipient::class;
    }

    protected function getCreateCampaignRules(): array
    {
        return [
            'subject_template' => ['required', 'string', 'max:255'],
            'body_template' => ['required', 'string', 'max:4000'],
        ];
    }

    protected function getCampaignCreateData(array $validated): array
    {
        return [
            'subject_template' => $validated['subject_template'],
            'body_template' => $validated['body_template'],
        ];
    }

    protected function mapContactData(Model $item, ?Collection $stages = null): array
    {
        $displayName = trim((string) ($item->contact_name ?: $item->name));
        $email = trim((string) ($item->contact_email ?? ''));

        $data = [
            'id' => $item->id,
            'type' => $item instanceof Lead ? 'lead' : 'customer',
            'display_name' => $displayName,
            'email' => $email,
            'secondary' => $item->company_name,
            'updated_at' => $item->updated_at,
        ];

        if ($item instanceof Lead && $stages) {
            $stageNameById = $stages->keyBy('id')->map(fn (LeadStage $s) => $s->name);
            $data['stage_id'] = $item->stage_id;
            $data['stage_name'] = $stageNameById->get($item->stage_id);
        } else {
            $data['stage_id'] = null;
            $data['stage_name'] = null;
        }

        return $data;
    }

    protected function createRecipients(Model $campaign, Collection $items, string $modelClass, array $validated, array &$missingContactIds): void
    {
        $subjectTemplate = (string) $validated['subject_template'];
        $bodyTemplate = (string) $validated['body_template'];

        foreach ($items as $item) {
            $email = trim((string) ($item->contact_email ?? ''));
            if ($email === '') {
                $missingContactIds[] = $item->id;
                continue;
            }

            $displayName = trim((string) ($item->contact_name ?: $item->name));
            $vars = $this->getTemplateVars($item, $modelClass);

            $renderedSubject = $this->renderTemplate($subjectTemplate, $vars);
            $renderedBody = $this->renderTemplate($bodyTemplate, $vars);

            EmailCampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'contactable_type' => $modelClass,
                'contactable_id' => $item->id,
                'display_name' => $displayName !== '' ? $displayName : null,
                'email' => $email,
                'rendered_subject' => $renderedSubject,
                'rendered_body' => $renderedBody,
                'status' => 'pending',
            ]);
        }
    }

    protected function getRecipientsData(Model $campaign): array
    {
        return [
            'source' => $campaign->source,
            'subject_template' => $campaign->subject_template,
            'body_template' => $campaign->body_template,
            'recipients' => $campaign->recipients
                ->sortBy('id')
                ->values()
                ->map(fn (EmailCampaignRecipient $r) => [
                    'id' => $r->id,
                    'contactable_type' => $r->contactable_type,
                    'contactable_id' => $r->contactable_id,
                    'display_name' => $r->display_name,
                    'email' => $r->email,
                    'rendered_subject' => $r->rendered_subject,
                    'rendered_body' => $r->rendered_body,
                    'status' => $r->status,
                    'queued_at' => $r->queued_at,
                    'sent_at' => $r->sent_at,
                    'error_message' => $r->error_message,
                ]),
        ];
    }

    /**
     * Send campaign emails
     */
    public function send(SendCampaignRequest $request, EmailCampaign $campaign): JsonResponse
    {
        $includeFailed = $request->boolean('include_failed', false);
        $statuses = $includeFailed ? ['pending', 'failed'] : ['pending'];

        $recipients = EmailCampaignRecipient::query()
            ->where('campaign_id', $campaign->id)
            ->whereIn('status', $statuses)
            ->orderBy('id')
            ->get();

        $queued = 0;
        $skipped = 0;

        foreach ($recipients as $r) {
            $email = trim(strtolower($r->email));
            if ($email === '') {
                $r->status = 'failed';
                $r->error_message = 'Email vacío.';
                $r->save();
                continue;
            }

            if (EmailUnsubscribe::query()->where('email', $email)->exists()) {
                $r->status = 'skipped';
                $r->error_message = 'Desuscrito.';
                $r->save();
                $skipped++;
                continue;
            }

            $r->status = 'queued';
            $r->queued_at = Carbon::now();
            $r->error_message = null;
            $r->save();

            SendEmailCampaignRecipientJob::dispatch($r->id)->onQueue('mail');
            $queued++;
        }

        if ($queued > 0 && $campaign->status !== 'sending') {
            $campaign->status = 'sending';
            $campaign->save();
        }

        return response()->json([
            'message' => 'Envío encolado.',
            'data' => [
                'queued' => $queued,
                'skipped_unsubscribed' => $skipped,
            ],
        ]);
    }
}
