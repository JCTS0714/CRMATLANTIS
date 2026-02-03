<?php

namespace App\Http\Controllers\Lead;

use App\DTOs\Lead\LeadResponseDto;
use App\Http\Requests\Lead\CreateLeadRequest;
use App\Http\Requests\Lead\MoveLeadStageRequest;
use App\Http\Requests\Lead\UpdateLeadRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Services\Config\ConfigService;
use App\Services\Lead\LeadService;
use App\Services\Lead\LeadValidationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

/**
 * LeadController - Handles CRUD operations for leads
 * 
 * Refactored: Data queries moved to LeadDataController
 * Refactored: Import functionality moved to LeadImportController
 * Refactored: Business logic moved to LeadService
 */
class LeadController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService,
        private readonly LeadValidationService $validationService,
        private readonly ConfigService $configService
    ) {}

    /**
     * Create a new lead
     */
    public function store(CreateLeadRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Perform custom document validation
        $validator = Validator::make($validated, []);
        $this->validationService->validateDocument(
            $validator,
            $validated['document_type'] ?? null,
            $validated['document_number'] ?? null
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $lead = $this->leadService->create($validated, $request->user()?->id);

            // Load stage relationship for DTO
            $lead->load('stage');

            return response()->json([
                'message' => 'Lead creado.',
                'data' => LeadResponseDto::fromModel($lead)->toArray(),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se pudo crear el lead. Revisa la configuración de la base de datos.',
            ], 422);
        }
    }

    /**
     * Update an existing lead
     */
    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        $validated = $request->validated();

        // Perform custom document validation
        $validator = Validator::make($validated, []);
        $this->validationService->validateDocument(
            $validator,
            $validated['document_type'] ?? null,
            $validated['document_number'] ?? null,
            $lead->id
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updated = $this->leadService->update($lead, $validated);

            // Load stage relationship for DTO
            $updated->load('stage');

            return response()->json([
                'message' => 'Lead actualizado.',
                'data' => LeadResponseDto::fromModel($updated)->toArray(),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Move lead to a different stage
     */
    public function moveStage(MoveLeadStageRequest $request, Lead $lead): JsonResponse
    {
        if ($lead->archived_at) {
            return response()->json([
                'message' => 'Este lead está archivado y no se puede mover de etapa.',
            ], 422);
        }

        $currentStageIsWon = $this->configService->isWonStage($lead->stage_id);
        if ($lead->won_at || $currentStageIsWon) {
            return response()->json([
                'message' => 'Este lead ya está GANADO y no se puede mover de columna. Solo se puede archivar.',
            ], 422);
        }

        $validated = $request->validated();
        $targetStage = $this->configService->getLeadStageById($validated['stage_id']);

        if (!$targetStage) {
            return response()->json([
                'message' => 'La etapa especificada no existe.',
            ], 404);
        }

        $updatedLead = DB::transaction(function () use ($lead, $targetStage) {
            if ($targetStage->is_won && !$lead->customer_id) {
                $customer = $this->leadService->convertToCustomer($lead);
                // Lead is archived by convertToCustomer
                return $lead->fresh();
            }

            $lead->stage_id = $targetStage->id;
            $maxPos = Lead::query()->where('stage_id', $targetStage->id)->max('position') ?? 0;
            $lead->position = ((int) $maxPos) + 1;
            $lead->save();

            return $lead->fresh();
        });

        return response()->json([
            'message' => 'Lead actualizado.',
            'data' => LeadResponseDto::fromModel($updatedLead)->toArray(),
        ]);
    }

    /**
     * Archive a won lead
     */
    public function archive(Lead $lead): JsonResponse
    {
        if ($lead->archived_at) {
            return response()->json([
                'message' => 'Este lead ya está archivado.',
                'data' => $lead,
            ]);
        }

        $currentStageIsWon = $this->configService->isWonStage($lead->stage_id);
        if (!$currentStageIsWon && !$lead->won_at) {
            return response()->json([
                'message' => 'Solo se puede archivar un lead que esté en la columna GANADO o ya marcado como ganado.',
            ], 422);
        }

        try {
            $archived = $this->leadService->archive($lead);

            // Load stage relationship for DTO
            $archived->load('stage');

            return response()->json([
                'message' => 'Lead archivado.',
                'data' => LeadResponseDto::fromModel($archived)->toArray(),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

}
