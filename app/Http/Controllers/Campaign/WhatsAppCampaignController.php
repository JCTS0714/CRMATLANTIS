<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Requests\Campaign\CreateWhatsAppCampaignRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class WhatsAppCampaignController extends BaseCampaignController
{
    protected function getContactField(): string
    {
        return 'phone';
    }

    protected function getCampaignModel(): string
    {
        return WhatsAppCampaign::class;
    }

    protected function getRecipientModel(): string
    {
        return WhatsAppCampaignRecipient::class;
    }

    protected function getCreateCampaignRules(): array
    {
        return [
            'message_template' => ['required', 'string', 'max:4000'],
        ];
    }

    protected function getCampaignCreateData(array $validated): array
    {
        return [
            'message_template' => $validated['message_template'],
        ];
    }

    protected function mapContactData(Model $item, ?Collection $stages = null): array
    {
        $displayName = trim((string) ($item->contact_name ?: $item->name));
        $phone = trim((string) ($item->contact_phone ?? ''));

        $data = [
            'id' => $item->id,
            'type' => $item instanceof Lead ? 'lead' : 'customer',
            'display_name' => $displayName,
            'phone' => $phone,
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
        $template = (string) $validated['message_template'];

        if (trim($template) === '') {
            throw new \InvalidArgumentException('El template de mensaje no puede estar vacío.');
        }

        foreach ($items as $item) {
            $phone = trim((string) ($item->contact_phone ?? ''));
            if ($phone === '') {
                $missingContactIds[] = $item->id;
                continue;
            }

            $displayName = trim((string) ($item->contact_name ?: $item->name));
            $vars = $this->getTemplateVars($item, $modelClass);

            $rendered = $this->renderTemplate($template, $vars);

            if (trim($rendered) === '') {
                $missingContactIds[] = $item->id;
                continue;
            }

            WhatsAppCampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'contactable_type' => $modelClass,
                'contactable_id' => $item->id,
                'display_name' => $displayName !== '' ? $displayName : null,
                'phone' => $phone,
                'rendered_message' => $rendered,
                'status' => 'pending',
            ]);
        }
    }

    protected function getRecipientsData(Model $campaign): array
    {
        return [
            'message_template' => $campaign->message_template,
            'recipients' => $campaign->recipients
                ->sortBy('id')
                ->values()
                ->map(fn (WhatsAppCampaignRecipient $r) => [
                    'id' => $r->id,
                    'contactable_type' => $r->contactable_type,
                    'contactable_id' => $r->contactable_id,
                    'display_name' => $r->display_name,
                    'phone' => $r->phone,
                    'rendered_message' => $r->rendered_message,
                    'status' => $r->status,
                    'opened_at' => $r->opened_at,
                    'sent_at' => $r->sent_at,
                    'error_message' => $r->error_message,
                ]),
        ];
    }

    /**
     * Update recipient status (WhatsApp specific)
     */
    public function updateRecipient(Request $request, WhatsAppCampaignRecipient $recipient): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'opened', 'sent', 'skipped', 'failed'])],
            'error_message' => ['nullable', 'string', 'max:255'],
        ]);

        $status = $validated['status'];

        $recipient->status = $status;
        $recipient->error_message = $validated['error_message'] ?? null;

        if ($status === 'opened' && !$recipient->opened_at) {
            $recipient->opened_at = Carbon::now();
        }

        if ($status === 'sent' && !$recipient->sent_at) {
            $recipient->sent_at = Carbon::now();
        }

        $recipient->save();

        return response()->json([
            'message' => 'Estado actualizado.',
            'data' => [
                'recipient' => [
                    'id' => $recipient->id,
                    'status' => $recipient->status,
                    'opened_at' => $recipient->opened_at,
                    'sent_at' => $recipient->sent_at,
                    'error_message' => $recipient->error_message,
                ],
            ],
        ]);
    }
}
