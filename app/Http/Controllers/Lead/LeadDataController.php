<?php

namespace App\Http\Controllers\Lead;

use App\DTOs\Lead\LeadCollectionResponseDto;
use App\DTOs\Lead\LeadResponseDto;
use App\DTOs\Lead\StageResponseDto;
use App\DTOs\Shared\PaginationDto;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Repositories\Lead\LeadRepositoryInterface;
use App\Services\Config\ConfigService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadDataController extends Controller
{
    public function __construct(
        private readonly LeadRepositoryInterface $leadRepository,
        private readonly ConfigService $configService
    ) {}

    /**
     * Get leads data for table/list view with pagination
     */
    public function tableData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => ['nullable', 'integer', Rule::exists('lead_stages', 'id')],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);
        $query = trim((string) ($validated['q'] ?? ''));
        $stageId = $validated['stage_id'] ?? null;

        // Get all stages (cached)
        $stages = $this->configService->getLeadStages();

        $stageIds = $stages->pluck('id')->values();

        // Count by stage using repository
        $countsByStage = $this->leadRepository->countByStage([
            'stageIds' => $stageIds->toArray(),
            'search' => $query !== '' ? $query : null,
        ]);

        $totalCount = $countsByStage->sum();

        // Get paginated leads using repository
        $filters = [
            'stageIds' => $stageIds->toArray(),
            'search' => $query !== '' ? $query : null,
        ];

        if ($stageId) {
            $filters['stageId'] = $stageId;
        }

        $paginator = $this->leadRepository->findForTable($filters, $perPage);

        // Transform to DTOs
        $leadDtos = collect($paginator->items())->map(function (Lead $lead) {
            return LeadResponseDto::fromModel($lead, includeRelations: true);
        });

        $stageDtos = $stages->map(function (LeadStage $stage) use ($countsByStage) {
            return StageResponseDto::fromModel($stage, $countsByStage->get($stage->id) ?? 0);
        });

        $response = new LeadCollectionResponseDto(
            stages: $stageDtos,
            leads: $leadDtos,
            totalCount: (int) $totalCount,
            pagination: PaginationDto::fromPaginator($paginator),
            filters: [
                'stage_id' => $stageId,
                'q' => $query,
            ],
        );

        return response()->json([
            'data' => $response->toArray(),
        ]);
    }

    /**
     * Get leads data for board/kanban view
     */
    public function boardData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:0'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        // Default: 20 leads per column
        $limit = isset($validated['limit']) ? (int) $validated['limit'] : 20;

        $start = isset($validated['date_from']) ? Carbon::parse($validated['date_from'])->startOfDay() : null;
        $end = isset($validated['date_to']) ? Carbon::parse($validated['date_to'])->endOfDay() : null;

        // Get all stages (cached)
        $stages = $this->configService->getLeadStages();

        $stageIds = $stages->pluck('id')->values();

        // Get counts per stage using repository
        $countsByStage = $this->leadRepository->countByStage([
            'stageIds' => $stageIds->toArray(),
            'dateFrom' => $start?->toDateTimeString(),
            'dateTo' => $end?->toDateTimeString(),
        ]);

        // For each stage retrieve leads using repository
        $stagesData = $stages->map(function (LeadStage $stage) use ($limit, $countsByStage, $start, $end) {
            $leads = $this->leadRepository->findForBoard([
                'stageIds' => [$stage->id],
                'dateFrom' => $start?->toDateTimeString(),
                'dateTo' => $end?->toDateTimeString(),
            ], $limit);

            // Transform leads to DTOs
            $leadDtos = $leads->map(fn($lead) => LeadResponseDto::fromModel($lead, includeRelations: false));

            return StageResponseDto::fromModel(
                $stage,
                $countsByStage->get($stage->id) ?? 0
            )->toArray() + ['leads' => $leadDtos->map->toArray()->values()];
        })->values();

        return response()->json([
            'data' => [
                'stages' => $stagesData,
            ],
        ]);
    }

    /**
     * Reorder leads within a stage
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => ['required', 'integer', Rule::exists('lead_stages', 'id')],
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', Rule::exists('leads', 'id')],
        ]);

        $ordered = $validated['ordered_ids'];

        DB::transaction(function () use ($ordered, $validated) {
            $total = count($ordered);
            foreach ($ordered as $index => $id) {
                // Only update leads that belong to the stage to avoid accidental cross-stage changes
                Lead::query()
                    ->where('id', $id)
                    ->where('stage_id', $validated['stage_id'])
                    ->update(['position' => ($total - $index)]);
            }
        });

        return response()->json([
            'message' => 'Orden actualizado.',
        ]);
    }
}
