<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadStage;
use Carbon\Carbon;
use SplFileObject;

class ProspectosCsvImporter
{
    /**
     * Importa el CSV legado de "prospectos" como leads.
     *
     * @return array{created:int,updated:int,skipped:int,invalid:int,mode:string}
     */
    public function import(string $filePath, array $options = []): array
    {
        $dryRun = (bool) ($options['dryRun'] ?? false);
        $updateExisting = (bool) ($options['updateExisting'] ?? false);
        $createdBy = $options['createdBy'] ?? null;
        $createdBy = is_numeric($createdBy) ? (int) $createdBy : null;

        if (!is_file($filePath)) {
            throw new \RuntimeException("No existe el archivo: {$filePath}");
        }

        $stageId = (int) LeadStage::query()->where('key', 'follow_up')->value('id');
        if (!$stageId) {
            $stageId = (int) LeadStage::query()->orderBy('sort_order')->orderBy('id')->value('id');
        }
        if (!$stageId) {
            throw new \RuntimeException('No hay etapas de leads en la BD.');
        }

        $normalize = static function ($value): string {
            $value = trim((string) $value);
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value; // UTF-8 BOM
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            $value = is_string($ascii) ? $ascii : $value;
            $value = mb_strtolower($value);
            $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? $value;
            $value = trim($value, '_');
            return $value;
        };

        $nullish = static function ($value): ?string {
            $v = trim((string) $value);
            if ($v === '') return null;
            $lv = mb_strtolower($v);
            if ($lv === 'nn') return null;
            if ($lv === 'null') return null;
            return $v;
        };

        $digitsOnly = static function ($value): ?string {
            $v = preg_replace('/\D+/', '', (string) $value);
            $v = is_string($v) ? trim($v) : '';
            if ($v === '') return null;

            // Common placeholder/dummy document numbers used by legacy systems
            $placeholders = [
                '55555555', '00000000', '12345678', '99999999', '11111111', '22222222'
            ];

            if (in_array($v, $placeholders, true)) {
                return null;
            }

            return $v !== '' ? $v : null;
        };

        $inferDocumentType = static function (?string $documentNumber): ?string {
            if (!$documentNumber) return null;
            $len = strlen($documentNumber);
            if ($len === 8) return 'dni';
            if ($len === 11) return 'ruc';
            return null;
        };

        $parseDate = static function ($value): ?Carbon {
            $value = trim((string) $value);
            if ($value === '') return null;
            try {
                return Carbon::parse($value);
            } catch (\Throwable $e) {
                return null;
            }
        };

        $firstLine = '';
        $handle = fopen($filePath, 'rb');
        if ($handle !== false) {
            $firstLine = (string) fgets($handle);
            fclose($handle);
        }

        $commaCount = substr_count($firstLine, ',');
        $semiCount = substr_count($firstLine, ';');
        $delimiter = $semiCount > $commaCount ? ';' : ',';

        $csv = new SplFileObject($filePath, 'rb');
        $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $csv->setCsvControl($delimiter);

        $headers = null;
        $mappedKeys = [];

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $invalid = 0;

        foreach ($csv as $row) {
            if (!is_array($row) || (count($row) === 1 && ($row[0] === null || trim((string) $row[0]) === ''))) {
                continue;
            }

            if ($headers === null) {
                $headers = $row;
                $mappedKeys = array_map($normalize, $headers);
                continue;
            }

            $data = [];
            foreach ($mappedKeys as $i => $key) {
                if ($key === '') continue;
                $data[$key] = $row[$i] ?? null;
            }

            $nombre = $nullish($data['nombre'] ?? null);
            $telefono = $nullish($data['telefono'] ?? $data['telefono_'] ?? null);
            $empresa = $nullish($data['empresa'] ?? null);
            $documento = $digitsOnly($data['documento'] ?? null);
            $observacion = $nullish($data['observacion'] ?? null);
            $migracion = $parseDate($data['migracion'] ?? null);

            if (!$nombre && !$empresa && !$telefono && !$documento) {
                $skipped++;
                continue;
            }

            $docType = $inferDocumentType($documento);

            $ciudad = $nullish($data['ciudad'] ?? null);
            $referencia = $nullish($data['referencia'] ?? null);
            $direccionParts = array_values(array_filter([
                $ciudad ? "Ciudad: {$ciudad}" : null,
                $referencia ? "Ref: {$referencia}" : null,
            ]));
            $companyAddress = $direccionParts ? mb_substr(implode(' | ', $direccionParts), 0, 255) : null;

            $createdAt = $parseDate($data['fecha_creacion'] ?? null) ?? now();
            $contactAt = $parseDate($data['fecha_contacto'] ?? null);
            $updatedAt = $contactAt && $contactAt->greaterThan($createdAt) ? $contactAt : $createdAt;

            if ($documento && !$docType) {
                // Documento con longitud no estándar: no lo guardamos para no romper validaciones futuras.
                $documento = null;
            }

            $payload = [
                'stage_id' => $stageId,
                'created_by' => $createdBy,
                'name' => $nombre ?? $empresa ?? ($telefono ? "Prospecto {$telefono}" : 'Prospecto'),
                'contact_name' => $nombre,
                'contact_phone' => $telefono,
                'company_name' => $empresa,
                'company_address' => $companyAddress,
                'document_type' => $docType,
                'document_number' => $documento,
                'observacion' => $observacion,
                'migracion' => $migracion?->toDateString(),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            $existing = null;
            if ($docType && $documento) {
                $existing = Lead::query()
                    ->whereNull('archived_at')
                    ->where('document_type', $docType)
                    ->where('document_number', $documento)
                    ->first();
            }

            if ($existing) {
                if (!$updateExisting) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $updated++;
                    continue;
                }

                $existing->fill($payload);
                $existing->save();
                $updated++;
                continue;
            }

            if ($dryRun) {
                $created++;
                continue;
            }

            try {
                Lead::query()->create($payload);
                $created++;
            } catch (\Throwable $e) {
                $invalid++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'invalid' => $invalid,
            'mode' => $dryRun ? 'DRY-RUN' : 'WRITE',
        ];
    }
}
