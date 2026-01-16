<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailCampaignRecipientJob;
use App\Models\Customer;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailUnsubscribe;
use App\Models\Lead;
use App\Models\LeadStage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmailCampaignController extends Controller
{
    public function recipients(Request $request): JsonResponse
    {
        if ($request->has('stage_id') && $request->input('stage_id') === '') {
            $request->merge(['stage_id' => null]);
        }

        if ($request->has('only_with_email') && $request->input('only_with_email') === '') {
            $request->merge(['only_with_email' => null]);
        }

        $validated = $request->validate([
            'source' => ['nullable', 'string', Rule::in(['leads', 'customers'])],
            'stage_id' => ['nullable', 'integer', Rule::exists('lead_stages', 'id')],
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'only_with_email' => ['nullable', Rule::in(['0', '1', 'true', 'false'])],
        ]);

        $source = (string) ($validated['source'] ?? 'leads');
        $stageId = $validated['stage_id'] ?? null;
        $query = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 200);
        $onlyWithEmail = $request->boolean('only_with_email', true);

        if ($source === 'customers' && !$request->user()?->can('customers.view')) {
            return response()->json([
                'message' => 'No tienes permisos para ver clientes.',
            ], 403);
        }

        $stages = collect();
        $contacts = collect();
        $totalCount = 0;
        $withEmailCount = 0;
        $withoutEmailCount = 0;

        if ($source === 'leads') {
            // Exclude "won" stages (Ganado). Those should be handled as Customers.
            $stages = LeadStage::query()
                ->where('is_won', false)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'key', 'name', 'sort_order', 'is_won']);

            $stageIds = $stages->pluck('id')->values();

            $filtered = Lead::query()
                ->whereIn('stage_id', $stageIds)
                ->whereNull('archived_at');

            if ($stageId) {
                if (!$stageIds->contains($stageId)) {
                    return response()->json([
                        'message' => 'La etapa seleccionada no es válida para este filtro (Ganado se excluye).',
                    ], 422);
                }

                $filtered->where('stage_id', $stageId);
            }

            if ($query !== '') {
                $filtered->where(function ($q) use ($query) {
                    $like = '%'.$query.'%';

                    $q->where('name', 'like', $like)
                        ->orWhere('company_name', 'like', $like)
                        ->orWhere('contact_name', 'like', $like)
                        ->orWhere('contact_email', 'like', $like)
                        ->orWhere('contact_phone', 'like', $like)
                        ->orWhere('document_number', 'like', $like);
                });
            }

            $baseQuery = clone $filtered;
            $totalCount = (int) (clone $baseQuery)->count();
            $withEmailCount = (int) (clone $baseQuery)
                ->whereNotNull('contact_email')
                ->where('contact_email', '!=', '')
                ->count();
            $withoutEmailCount = max(0, $totalCount - $withEmailCount);

            if ($onlyWithEmail) {
                $filtered->whereNotNull('contact_email')->where('contact_email', '!=', '');
            }

            $items = $filtered
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get([
                    'id',
                    'stage_id',
                    'name',
                    'contact_name',
                    'contact_email',
                    'company_name',
                    'updated_at',
                ]);

            $stageNameById = $stages->keyBy('id')->map(fn (LeadStage $s) => $s->name);

            $contacts = $items->map(function (Lead $lead) use ($stageNameById) {
                $displayName = trim((string) ($lead->contact_name ?: $lead->name));
                $email = trim((string) ($lead->contact_email ?? ''));

                return [
                    'id' => $lead->id,
                    'type' => 'lead',
                    'display_name' => $displayName,
                    'email' => $email,
                    'secondary' => $lead->company_name,
                    'stage_id' => $lead->stage_id,
                    'stage_name' => $stageNameById->get($lead->stage_id),
                    'updated_at' => $lead->updated_at,
                ];
            })->values();
        }

        if ($source === 'customers') {
            $filtered = Customer::query();

            if ($query !== '') {
                $filtered->where(function ($q) use ($query) {
                    $like = '%'.$query.'%';

                    $q->where('name', 'like', $like)
                        ->orWhere('company_name', 'like', $like)
                        ->orWhere('contact_name', 'like', $like)
                        ->orWhere('contact_email', 'like', $like)
                        ->orWhere('contact_phone', 'like', $like)
                        ->orWhere('document_number', 'like', $like);
                });
            }

            $baseQuery = clone $filtered;
            $totalCount = (int) (clone $baseQuery)->count();
            $withEmailCount = (int) (clone $baseQuery)
                ->whereNotNull('contact_email')
                ->where('contact_email', '!=', '')
                ->count();
            $withoutEmailCount = max(0, $totalCount - $withEmailCount);

            if ($onlyWithEmail) {
                $filtered->whereNotNull('contact_email')->where('contact_email', '!=', '');
            }

            $items = $filtered
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get([
                    'id',
                    'name',
                    'contact_name',
                    'contact_email',
                    'company_name',
                    'updated_at',
                ]);

            $contacts = $items->map(function (Customer $customer) {
                $displayName = trim((string) ($customer->contact_name ?: $customer->name));
                $email = trim((string) ($customer->contact_email ?? ''));

                return [
                    'id' => $customer->id,
                    'type' => 'customer',
                    'display_name' => $displayName,
                    'email' => $email,
                    'secondary' => $customer->company_name,
                    'stage_id' => null,
                    'stage_name' => null,
                    'updated_at' => $customer->updated_at,
                ];
            })->values();
        }

        return response()->json([
            'data' => [
                'source' => $source,
                'stages' => $stages->map(fn (LeadStage $s) => [
                    'id' => $s->id,
                    'key' => $s->key,
                    'name' => $s->name,
                    'sort_order' => $s->sort_order,
                    'is_won' => (bool) $s->is_won,
                ])->values(),
                'contacts' => $contacts,
                'counts' => [
                    'total' => (int) $totalCount,
                    'with_email' => (int) $withEmailCount,
                    'without_email' => (int) $withoutEmailCount,
                    'returned' => (int) $contacts->count(),
                ],
                'filters' => [
                    'source' => $source,
                    'stage_id' => $stageId,
                    'q' => $query,
                    'limit' => $limit,
                    'only_with_email' => $onlyWithEmail,
                ],
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $campaigns = EmailCampaign::query()
            ->withCount('recipients')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'name', 'source', 'subject_template', 'status', 'created_by', 'created_at', 'updated_at']);

        return response()->json([
            'data' => [
                'campaigns' => $campaigns,
            ],
        ]);
    }

    public function show(Request $request, EmailCampaign $campaign): JsonResponse
    {
        $campaign->load(['recipients']);

        return response()->json([
            'data' => [
                'campaign' => [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'source' => $campaign->source,
                    'subject_template' => $campaign->subject_template,
                    'body_template' => $campaign->body_template,
                    'status' => $campaign->status,
                    'created_by' => $campaign->created_by,
                    'created_at' => $campaign->created_at,
                    'updated_at' => $campaign->updated_at,
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
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'source' => ['required', 'string', Rule::in(['leads', 'customers'])],
            'subject_template' => ['required', 'string', 'max:255'],
            'body_template' => ['required', 'string', 'max:4000'],
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
        ]);

        $source = (string) $validated['source'];

        if ($source === 'customers' && !$request->user()?->can('customers.view')) {
            return response()->json([
                'message' => 'No tienes permisos para ver clientes.',
            ], 403);
        }

        $ids = collect($validated['ids'])->unique()->values();

        $modelClass = $source === 'customers' ? Customer::class : Lead::class;
        $query = $modelClass::query()->whereIn('id', $ids);

        if ($modelClass === Lead::class) {
            $query->select(['id', 'name', 'contact_name', 'contact_email', 'company_name']);
        } else {
            $query->select(['id', 'name', 'contact_name', 'contact_email', 'company_name']);
        }

        $items = $query->get();

        if ($items->count() !== $ids->count()) {
            return response()->json([
                'message' => 'Algunos destinatarios no existen o no son accesibles.',
            ], 422);
        }

        $subjectTemplate = (string) $validated['subject_template'];
        $bodyTemplate = (string) $validated['body_template'];

        $missingEmailIds = [];

        $campaign = DB::transaction(function () use ($request, $validated, $items, $subjectTemplate, $bodyTemplate, $modelClass, &$missingEmailIds) {
            $campaign = EmailCampaign::create([
                'created_by' => $request->user()?->id,
                'name' => $validated['name'] ?? null,
                'source' => $validated['source'],
                'subject_template' => $subjectTemplate,
                'body_template' => $bodyTemplate,
                'status' => 'draft',
            ]);

            foreach ($items as $item) {
                $email = trim((string) ($item->contact_email ?? ''));
                if ($email === '') {
                    $missingEmailIds[] = $item->id;
                    continue;
                }

                $displayName = trim((string) ($item->contact_name ?: $item->name));

                $vars = [
                    'first_name' => $this->firstName($displayName),
                    'full_name' => $displayName,
                    'lead_name' => $modelClass === Lead::class ? (string) $item->name : '',
                    'contact_name' => (string) ($item->contact_name ?? ''),
                    'company_name' => (string) ($item->company_name ?? ''),
                ];

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

            return $campaign;
        });

        return response()->json([
            'message' => 'Campaña creada.',
            'data' => [
                'campaign_id' => $campaign->id,
                'source' => $source,
                'skipped_missing_email_ids' => $missingEmailIds,
            ],
        ]);
    }

    public function send(Request $request, EmailCampaign $campaign): JsonResponse
    {
        $validated = $request->validate([
            'include_failed' => ['nullable', Rule::in(['0', '1', 'true', 'false'])],
        ]);

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

    private function firstName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        return (string) ($parts[0] ?? $name);
    }

    private function renderTemplate(string $template, array $vars): string
    {
        $out = $template;
        foreach ($vars as $key => $value) {
            $out = str_replace('{{'.$key.'}}', (string) $value, $out);
        }

        $out = preg_replace('/\{\{\s*[a-zA-Z0-9_]+\s*\}\}/', '', $out) ?? $out;

        return trim($out);
    }
}
