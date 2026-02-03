<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lead\ImportLeadsRequest;
use App\Services\ProspectosCsvImporter;
use Illuminate\Http\JsonResponse;

class LeadImportController extends Controller
{
    /**
     * Import leads from CSV file
     */
    public function import(ImportLeadsRequest $request, ProspectosCsvImporter $importer): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->getRealPath();

        if (!$path) {
            return response()->json([
                'message' => 'No se pudo leer el archivo CSV.',
            ], 422);
        }

        try {
            $result = $importer->import($path, [
                'dryRun' => false,
                'updateExisting' => false,
                'createdBy' => $request->user()?->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'No se pudo importar el CSV.',
            ], 422);
        }

        return response()->json([
            'message' => 'Importación completada.',
            'data' => $result,
        ]);
    }
}
