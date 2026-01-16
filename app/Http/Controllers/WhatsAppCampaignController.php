<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WhatsAppCampaignController extends Controller
{
    public function recipients(Request $request): JsonResponse
    {
        // Normalize empty-string query params (axios may send null as "")
        if ($request->has('stage_id') && $request->input('stage_id') === '') {
            $request->merge(['stage_id' => null]);
        }

        if ($request->has('only_with_phone') && $request->input('only_with_phone') === '') {
            $request->merge(['only_with_phone' => null]);
        }

        $validated = $request->validate([
            'source' => ['nullable', 'string', Rule::in(['leads', 'customers'])],
            'stage_id' => ['nullable', 'integer', Rule::exists('lead_stages', 'id')],
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            // For querystrings, Laravel's boolean validator only accepts 0/1.
            // Accept true/false as strings too, then cast with Request::boolean().
            'only_with_phone' => ['nullable', Rule::in(['0', '1', 'true', 'false'])],
        ]);

        $source = (string) ($validated['source'] ?? 'leads');
        $stageId = $validated['stage_id'] ?? null;
        $query = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 200);
        $onlyWithPhone = $request->boolean('only_with_phone', true);

        if ($source === 'customers' && !$request->user()?->can('customers.view')) {
            return response()->json([
                'message' => 'No tienes permisos para ver clientes.',
            ], 403);
        }

        $stages = collect();
        $contacts = collect();
        $totalCount = 0;
        $withPhoneCount = 0;
        $withoutPhoneCount = 0;

        if ($source === 'leads') {
            // For WhatsApp outreach, we exclude "won" stages (Ganado), because those
            // leads are already converted and should be handled as Customers.
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
            $withPhoneCount = (int) (clone $baseQuery)
                ->whereNotNull('contact_phone')
                ->where('contact_phone', '!=', '')
                ->count();
            $withoutPhoneCount = max(0, $totalCount - $withPhoneCount);

            if ($onlyWithPhone) {
                $filtered->whereNotNull('contact_phone')->where('contact_phone', '!=', '');
            }

            $items = $filtered
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get([
                    'id',
                    'stage_id',
                    'name',
                    'contact_name',
                    'contact_phone',
                    'company_name',
                    'updated_at',
                ]);

            $stageNameById = $stages->keyBy('id')->map(fn (LeadStage $s) => $s->name);

            $contacts = $items->map(function (Lead $lead) use ($stageNameById) {
                $displayName = trim((string) ($lead->contact_name ?: $lead->name));
                $phone = trim((string) ($lead->contact_phone ?? ''));

                return [
                    'id' => $lead->id,
                    'type' => 'lead',
                    'display_name' => $displayName,
                    'phone' => $phone,
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
            $withPhoneCount = (int) (clone $baseQuery)
                ->whereNotNull('contact_phone')
                ->where('contact_phone', '!=', '')
                ->count();
            $withoutPhoneCount = max(0, $totalCount - $withPhoneCount);

            if ($onlyWithPhone) {
                $filtered->whereNotNull('contact_phone')->where('contact_phone', '!=', '');
            }

            $items = $filtered
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get([
                    'id',
                    'name',
                    'contact_name',
                    'contact_phone',
                    'company_name',
                    'updated_at',
                ]);

            $contacts = $items->map(function (Customer $customer) {
                $displayName = trim((string) ($customer->contact_name ?: $customer->name));
                $phone = trim((string) ($customer->contact_phone ?? ''));

                return [
                    'id' => $customer->id,
                    'type' => 'customer',
                    'display_name' => $displayName,
                    'phone' => $phone,
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
                    'with_phone' => (int) $withPhoneCount,
                    'without_phone' => (int) $withoutPhoneCount,
                    'returned' => (int) $contacts->count(),
                ],
                'filters' => [
                    'source' => $source,
                    'stage_id' => $stageId,
                    'q' => $query,
                    'limit' => $limit,
                    'only_with_phone' => $onlyWithPhone,
                ],
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $campaigns = WhatsAppCampaign::query()
            ->withCount('recipients')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'name', 'status', 'created_by', 'created_at', 'updated_at']);

        return response()->json([
            'data' => [
                'campaigns' => $campaigns,
            ],
        ]);
    }

    public function show(Request $request, WhatsAppCampaign $campaign): JsonResponse
    {
        $campaign->load(['recipients']);

        return response()->json([
            'data' => [
                'campaign' => [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'message_template' => $campaign->message_template,
                    'status' => $campaign->status,
                    'created_by' => $campaign->created_by,
                    'created_at' => $campaign->created_at,
                    'updated_at' => $campaign->updated_at,
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
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'message_template' => ['required', 'string', 'max:4000'],
            'source' => ['required', 'string', Rule::in(['leads', 'customers'])],
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
            $query->select(['id', 'name', 'contact_name', 'contact_phone', 'company_name']);
        } else {
            $query->select(['id', 'name', 'contact_name', 'contact_phone', 'company_name']);
        }

        $items = $query->get();

        if ($items->count() !== $ids->count()) {
            return response()->json([
                'message' => 'Algunos destinatarios no existen o no son accesibles.',
            ], 422);
        }

        $template = (string) $validated['message_template'];

        $missingPhoneIds = [];

        $campaign = DB::transaction(function () use ($request, $validated, $items, $template, $modelClass, &$missingPhoneIds) {
            $campaign = WhatsAppCampaign::create([
                'created_by' => $request->user()?->id,
                'name' => $validated['name'] ?? null,
                'message_template' => $template,
                'status' => 'draft',
            ]);

            foreach ($items as $item) {
                $phone = trim((string) ($item->contact_phone ?? ''));
                if ($phone === '') {
                    $missingPhoneIds[] = $item->id;
                    continue;
                }

                $displayName = trim((string) ($item->contact_name ?: $item->name));

                $rendered = $this->renderTemplate($template, [
                    'first_name' => $this->firstName($displayName),
                    'full_name' => $displayName,
                    'lead_name' => $modelClass === Lead::class ? (string) $item->name : '',
                    'contact_name' => (string) ($item->contact_name ?? ''),
                    'company_name' => (string) ($item->company_name ?? ''),
                ]);

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

            return $campaign;
        });

        return response()->json([
            'message' => 'Campaña creada.',
            'data' => [
                'campaign_id' => $campaign->id,
                'source' => $source,
                'skipped_missing_phone_ids' => $missingPhoneIds,
            ],
        ]);
    }

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

        // Clean leftovers like {{unknown}}
        $out = preg_replace('/\{\{\s*[a-zA-Z0-9_]+\s*\}\}/', '', $out) ?? $out;

        return trim($out);
    }
}
