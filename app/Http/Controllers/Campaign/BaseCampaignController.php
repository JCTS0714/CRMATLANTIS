<?php

namespace App\Http\Controllers\Campaign;

use App\DTOs\Campaign\EmailCampaignResponseDto;
use App\DTOs\Campaign\WhatsAppCampaignResponseDto;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Services\Config\ConfigService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

abstract class BaseCampaignController extends Controller
{
    public function __construct(
        protected readonly ConfigService $configService
    ) {}

    /**
     * Get the contact field name (email, phone, etc.)
     */
    abstract protected function getContactField(): string;

    /**
     * Get the campaign model class
     */
    abstract protected function getCampaignModel(): string;

    /**
     * Get the recipient model class
     */
    abstract protected function getRecipientModel(): string;

    /**
     * Get additional validation rules for campaign creation
     */
    abstract protected function getCreateCampaignRules(): array;

    /**
     * Get additional data to be saved when creating campaign
     */
    abstract protected function getCampaignCreateData(array $validated): array;

    /**
     * Map contact data for response
     */
    abstract protected function mapContactData(Model $item, ?Collection $stages = null): array;

    /**
     * Create recipients for campaign
     */
    abstract protected function createRecipients(Model $campaign, Collection $items, string $modelClass, array $validated, array &$missingContactIds): void;

    /**
     * Get recipients for viewing campaign details
     */
    abstract protected function getRecipientsData(Model $campaign): array;

    /**
     * Get contact validation param name
     */
    protected function getContactFilterParam(): string
    {
        return 'only_with_' . $this->getContactField();
    }

    /**
     * Get all recipients (shared endpoint logic)
     */
    public function recipients(Request $request): JsonResponse
    {
        $this->normalizeEmptyParams($request);

        $validated = $request->validate([
            'source' => ['nullable', 'string', Rule::in(['leads', 'customers'])],
            'stage_id' => ['nullable', 'integer', Rule::exists('lead_stages', 'id')],
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            $this->getContactFilterParam() => ['nullable', Rule::in(['0', '1', 'true', 'false'])],
        ]);

        $source = (string) ($validated['source'] ?? 'leads');
        $stageId = $validated['stage_id'] ?? null;
        $query = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 200);
        $onlyWithContact = $request->boolean($this->getContactFilterParam(), true);

        if ($source === 'customers' && !$request->user()?->can('customers.view')) {
            return response()->json([
                'message' => 'No tienes permisos para ver clientes.',
            ], 403);
        }

        $stages = collect();
        $contacts = collect();
        $totalCount = 0;
        $withContactCount = 0;
        $withoutContactCount = 0;

        if ($source === 'leads') {
            [$contacts, $stages, $totalCount, $withContactCount, $withoutContactCount] = $this->getLeadRecipients(
                $stageId,
                $query,
                $limit,
                $onlyWithContact
            );
        } elseif ($source === 'customers') {
            [$contacts, $totalCount, $withContactCount, $withoutContactCount] = $this->getCustomerRecipients(
                $query,
                $limit,
                $onlyWithContact
            );
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
                    'with_' . $this->getContactField() => (int) $withContactCount,
                    'without_' . $this->getContactField() => (int) $withoutContactCount,
                    'returned' => (int) $contacts->count(),
                ],
                'filters' => [
                    'source' => $source,
                    'stage_id' => $stageId,
                    'q' => $query,
                    'limit' => $limit,
                    $this->getContactFilterParam() => $onlyWithContact,
                ],
            ],
        ]);
    }

    /**
     * Get list of campaigns
     */
    public function index(Request $request): JsonResponse
    {
        $campaignModel = $this->getCampaignModel();

        $campaigns = $campaignModel::query()
            ->withCount('recipients')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $dtoClass = $campaignModel === \App\Models\EmailCampaign::class
            ? EmailCampaignResponseDto::class
            : WhatsAppCampaignResponseDto::class;

        return response()->json([
            'data' => [
                'campaigns' => $campaigns->map(fn($c) => $dtoClass::fromModel($c, includeBody: false)->toArray()),
            ],
        ]);
    }

    /**
     * Show single campaign with recipients
     */
    public function show(Request $request, $campaignId): JsonResponse
    {
        $campaignModel = $this->getCampaignModel();
        $campaign = $campaignModel::with(['recipients'])->findOrFail($campaignId);

        $campaignData = array_merge([
            'id' => $campaign->id,
            'name' => $campaign->name,
            'status' => $campaign->status,
            'created_by' => $campaign->created_by,
            'created_at' => $campaign->created_at,
            'updated_at' => $campaign->updated_at,
        ], $this->getRecipientsData($campaign));

        return response()->json([
            'data' => [
                'campaign' => $campaignData,
            ],
        ]);
    }

    /**
     * Store new campaign
     */
    public function store(Request $request): JsonResponse
    {
        $baseRules = [
            'name' => ['nullable', 'string', 'max:255'],
            'source' => ['required', 'string', Rule::in(['leads', 'customers'])],
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
        ];

        $validated = $request->validate(array_merge($baseRules, $this->getCreateCampaignRules()));

        $source = (string) $validated['source'];

        if ($source === 'customers' && !$request->user()?->can('customers.view')) {
            return response()->json([
                'message' => 'No tienes permisos para ver clientes.',
            ], 403);
        }

        $ids = collect($validated['ids'])->unique()->values();
        $modelClass = $source === 'customers' ? Customer::class : Lead::class;

        $items = $this->fetchContactItems($modelClass, $ids);

        if ($items->count() !== $ids->count()) {
            return response()->json([
                'message' => 'Algunos destinatarios no existen o no son accesibles.',
            ], 422);
        }

        $missingContactIds = [];

        $campaign = \DB::transaction(function () use ($request, $validated, $items, $modelClass, &$missingContactIds) {
            $campaignModel = $this->getCampaignModel();

            $campaign = $campaignModel::create(array_merge([
                'created_by' => $request->user()?->id,
                'name' => $validated['name'] ?? null,
                'source' => $validated['source'],
                'status' => 'draft',
            ], $this->getCampaignCreateData($validated)));

            $this->createRecipients($campaign, $items, $modelClass, $validated, $missingContactIds);

            return $campaign;
        });

        $dtoClass = $modelClass === \App\Models\Customer::class
            ? ($this->getCampaignModel() === \App\Models\EmailCampaign::class ? EmailCampaignResponseDto::class : WhatsAppCampaignResponseDto::class)
            : ($this->getCampaignModel() === \App\Models\EmailCampaign::class ? EmailCampaignResponseDto::class : WhatsAppCampaignResponseDto::class);

        return response()->json([
            'message' => 'Campaña creada.',
            'data' => [
                'campaign' => $dtoClass::fromModel($campaign->fresh(), includeBody: false)->toArray(),
                'campaign_id' => $campaign->id,
                'skipped_missing_' . $this->getContactField() . '_ids' => $missingContactIds,
            ],
        ]);
    }

    /**
     * Helper: Normalize empty string params
     */
    protected function normalizeEmptyParams(Request $request): void
    {
        if ($request->has('stage_id') && $request->input('stage_id') === '') {
            $request->merge(['stage_id' => null]);
        }

        $filterParam = $this->getContactFilterParam();
        if ($request->has($filterParam) && $request->input($filterParam) === '') {
            $request->merge([$filterParam => null]);
        }
    }

    /**
     * Helper: Get lead recipients
     */
    protected function getLeadRecipients(?int $stageId, string $query, int $limit, bool $onlyWithContact): array
    {
        $stages = $this->configService->getActiveLeadStages();

        $stageIds = $stages->pluck('id')->values();

        $filtered = Lead::query()
            ->whereIn('stage_id', $stageIds)
            ->whereNull('archived_at');

        if ($stageId) {
            if (!$stageIds->contains($stageId)) {
                throw new \Exception('La etapa seleccionada no es válida para este filtro (Ganado se excluye).');
            }
            $filtered->where('stage_id', $stageId);
        }

        if ($query !== '') {
            $this->applySearchFilter($filtered, $query);
        }

        $baseQuery = clone $filtered;
        $totalCount = (int) (clone $baseQuery)->count();
        $withContactCount = (int) (clone $baseQuery)
            ->whereNotNull('contact_' . $this->getContactField())
            ->where('contact_' . $this->getContactField(), '!=', '')
            ->count();
        $withoutContactCount = max(0, $totalCount - $withContactCount);

        if ($onlyWithContact) {
            $filtered->whereNotNull('contact_' . $this->getContactField())
                ->where('contact_' . $this->getContactField(), '!=', '');
        }

        $items = $filtered
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get([
                'id',
                'stage_id',
                'name',
                'contact_name',
                'contact_' . $this->getContactField(),
                'company_name',
                'updated_at',
            ]);

        $contacts = $items->map(fn ($lead) => $this->mapContactData($lead, $stages))->values();

        return [$contacts, $stages, $totalCount, $withContactCount, $withoutContactCount];
    }

    /**
     * Helper: Get customer recipients
     */
    protected function getCustomerRecipients(string $query, int $limit, bool $onlyWithContact): array
    {
        $filtered = Customer::query();

        if ($query !== '') {
            $this->applySearchFilter($filtered, $query);
        }

        $baseQuery = clone $filtered;
        $totalCount = (int) (clone $baseQuery)->count();
        $withContactCount = (int) (clone $baseQuery)
            ->whereNotNull('contact_' . $this->getContactField())
            ->where('contact_' . $this->getContactField(), '!=', '')
            ->count();
        $withoutContactCount = max(0, $totalCount - $withContactCount);

        if ($onlyWithContact) {
            $filtered->whereNotNull('contact_' . $this->getContactField())
                ->where('contact_' . $this->getContactField(), '!=', '');
        }

        $items = $filtered
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get([
                'id',
                'name',
                'contact_name',
                'contact_' . $this->getContactField(),
                'company_name',
                'updated_at',
            ]);

        $contacts = $items->map(fn ($customer) => $this->mapContactData($customer))->values();

        return [$contacts, $totalCount, $withContactCount, $withoutContactCount];
    }

    /**
     * Helper: Apply search filter
     */
    protected function applySearchFilter(Builder $query, string $searchTerm): void
    {
        $query->where(function ($q) use ($searchTerm) {
            $like = '%' . $searchTerm . '%';

            $q->where('name', 'like', $like)
                ->orWhere('company_name', 'like', $like)
                ->orWhere('contact_name', 'like', $like)
                ->orWhere('contact_email', 'like', $like)
                ->orWhere('contact_phone', 'like', $like)
                ->orWhere('document_number', 'like', $like);
        });
    }

    /**
     * Helper: Fetch contact items
     */
    protected function fetchContactItems(string $modelClass, Collection $ids): Collection
    {
        $query = $modelClass::query()->whereIn('id', $ids);

        $query->select([
            'id',
            'name',
            'contact_name',
            'contact_' . $this->getContactField(),
            'company_name',
        ]);

        return $query->get();
    }

    /**
     * Helper: Extract first name from full name
     */
    protected function firstName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        return (string) ($parts[0] ?? $name);
    }

    /**
     * Helper: Render template with variables
     */
    protected function renderTemplate(string $template, array $vars): string
    {
        $out = $template;
        foreach ($vars as $key => $value) {
            $out = str_replace('{{' . $key . '}}', (string) $value, $out);
        }

        $out = preg_replace('/\{\{\s*[a-zA-Z0-9_]+\s*\}\}/', '', $out) ?? $out;

        return trim($out);
    }

    /**
     * Helper: Get template variables for contact
     */
    protected function getTemplateVars(Model $item, string $modelClass): array
    {
        $displayName = trim((string) ($item->contact_name ?: $item->name));

        return [
            'first_name' => $this->firstName($displayName),
            'full_name' => $displayName,
            'lead_name' => $modelClass === Lead::class ? (string) $item->name : '',
            'contact_name' => (string) ($item->contact_name ?? ''),
            'company_name' => (string) ($item->company_name ?? ''),
        ];
    }
}
