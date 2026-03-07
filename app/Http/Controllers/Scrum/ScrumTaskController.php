<?php

namespace App\Http\Controllers\Scrum;

use App\Http\Controllers\Controller;
use App\Models\ScrumTask;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScrumTaskController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:200'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $query = User::query()
            ->select(['id', 'name', 'email']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        $users = $query
            ->orderBy('name')
            ->paginate((int) ($validated['per_page'] ?? 100));

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', Rule::in(['alta', 'media', 'baja'])],
            'estado' => ['nullable', Rule::in(['pendiente', 'en_progreso', 'completada'])],
            'responsable_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:200'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $query = ScrumTask::query()->with([
            'asignador:id,name,email',
            'responsable:id,name,email',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('nombre', 'like', $like)
                    ->orWhere('descripcion', 'like', $like)
                    ->orWhere('observacion', 'like', $like);
            });
        }

        if (!empty($validated['priority'])) {
            $query->where('prioridad', $validated['priority']);
        }

        if (!empty($validated['estado'])) {
            $query->where('estado', $validated['estado']);
        }

        if (!empty($validated['responsable_id'])) {
            $query->where('responsable_id', $validated['responsable_id']);
        }

        $tasks = $query
            ->orderByDesc('updated_at')
            ->paginate((int) ($validated['per_page'] ?? 100))
            ->through(function (ScrumTask $task) {
                return [
                    'id' => $task->id,
                    'nombre' => $task->nombre,
                    'descripcion' => $task->descripcion,
                    'asignador_id' => $task->asignador_id,
                    'asignador' => $task->asignador?->name,
                    'responsable_id' => $task->responsable_id,
                    'responsable' => $task->responsable?->name,
                    'prioridad' => $task->prioridad,
                    'tiempo_ejecucion' => optional($task->tiempo_ejecucion)?->toDateTimeString(),
                    'observacion' => $task->observacion,
                    'estado' => $task->estado,
                    'estado_tiempo' => $this->resolveEstadoTiempo($task),
                    'created_at' => optional($task->created_at)?->toDateTimeString(),
                    'updated_at' => optional($task->updated_at)?->toDateTimeString(),
                ];
            });

        return response()->json([
            'data' => $tasks->items(),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'responsable_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'prioridad' => ['required', Rule::in(['alta', 'media', 'baja'])],
            'tiempo_ejecucion' => ['nullable', 'date'],
            'observacion' => ['nullable', 'string'],
            'asignador_id' => ['prohibited'],
            'asignador' => ['prohibited'],
        ]);

        $task = ScrumTask::query()->create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'asignador_id' => $userId,
            'responsable_id' => $validated['responsable_id'],
            'prioridad' => $validated['prioridad'],
            'tiempo_ejecucion' => $validated['tiempo_ejecucion'] ?? null,
            'observacion' => $validated['observacion'] ?? null,
            'estado' => 'pendiente',
        ]);

        $task->load(['asignador:id,name,email', 'responsable:id,name,email']);

        return response()->json([
            'message' => 'Tarea creada correctamente.',
            'data' => [
                'id' => $task->id,
                'nombre' => $task->nombre,
                'descripcion' => $task->descripcion,
                'asignador_id' => $task->asignador_id,
                'asignador' => $task->asignador?->name,
                'responsable_id' => $task->responsable_id,
                'responsable' => $task->responsable?->name,
                'prioridad' => $task->prioridad,
                'tiempo_ejecucion' => optional($task->tiempo_ejecucion)?->toDateTimeString(),
                'observacion' => $task->observacion,
                'estado' => $task->estado,
                'estado_tiempo' => $this->resolveEstadoTiempo($task),
            ],
        ], 201);
    }

    public function update(Request $request, ScrumTask $scrumTask): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'responsable_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'prioridad' => ['required', Rule::in(['alta', 'media', 'baja'])],
            'tiempo_ejecucion' => ['nullable', 'date'],
            'observacion' => ['nullable', 'string'],
            'asignador_id' => ['prohibited'],
            'asignador' => ['prohibited'],
        ]);

        $scrumTask->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'responsable_id' => $validated['responsable_id'],
            'prioridad' => $validated['prioridad'],
            'tiempo_ejecucion' => $validated['tiempo_ejecucion'] ?? null,
            'observacion' => $validated['observacion'] ?? null,
        ]);

        $scrumTask->load(['asignador:id,name,email', 'responsable:id,name,email']);

        return response()->json([
            'message' => 'Tarea actualizada correctamente.',
            'data' => [
                'id' => $scrumTask->id,
                'nombre' => $scrumTask->nombre,
                'descripcion' => $scrumTask->descripcion,
                'asignador_id' => $scrumTask->asignador_id,
                'asignador' => $scrumTask->asignador?->name,
                'responsable_id' => $scrumTask->responsable_id,
                'responsable' => $scrumTask->responsable?->name,
                'prioridad' => $scrumTask->prioridad,
                'tiempo_ejecucion' => optional($scrumTask->tiempo_ejecucion)?->toDateTimeString(),
                'observacion' => $scrumTask->observacion,
                'estado' => $scrumTask->estado,
                'estado_tiempo' => $this->resolveEstadoTiempo($scrumTask),
            ],
        ]);
    }

    public function updateStatus(Request $request, ScrumTask $scrumTask): JsonResponse
    {
        $validated = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'en_progreso', 'completada'])],
        ]);

        $scrumTask->update([
            'estado' => $validated['estado'],
        ]);

        return response()->json([
            'message' => 'Estado actualizado.',
            'data' => [
                'id' => $scrumTask->id,
                'estado' => $scrumTask->estado,
                'estado_tiempo' => $this->resolveEstadoTiempo($scrumTask),
            ],
        ]);
    }

    public function destroy(ScrumTask $scrumTask): JsonResponse
    {
        $scrumTask->delete();

        return response()->json([
            'message' => 'Tarea eliminada correctamente.',
        ]);
    }

    private function resolveEstadoTiempo(ScrumTask $task): int
    {
        if (!$task->tiempo_ejecucion) {
            return 0;
        }

        if ($task->estado === 'completada') {
            return 0;
        }

        return now()->gt($task->tiempo_ejecucion) ? 1 : 0;
    }
}
