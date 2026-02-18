<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Contador;
use App\Models\Certificado;
use App\Models\Customer;
use App\Models\Incidence;
use App\Models\IncidenceStage;
use App\Models\Lead;
use App\Models\LostLead;
use App\Models\User;
use App\Models\WaitingLead;
use App\Services\ProspectosCsvImporter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('crm:import:prospectos {file : Ruta al CSV exportado del sistema anterior} {--dry-run : No escribe en BD, solo valida} {--user= : ID de usuario para created_by} {--update-existing : Actualiza leads existentes (por DNI/RUC) en vez de saltarlos}', function () {
    $file = (string) $this->argument('file');
    $dryRun = (bool) $this->option('dry-run');
    $updateExisting = (bool) $this->option('update-existing');
    $createdBy = $this->option('user');
    $createdBy = is_numeric($createdBy) ? (int) $createdBy : null;

    try {
        $importer = app()->make(ProspectosCsvImporter::class);
        $result = $importer->import($file, [
            'dryRun' => $dryRun,
            'updateExisting' => $updateExisting,
            'createdBy' => $createdBy,
        ]);
    } catch (\Throwable $e) {
        $this->error($e->getMessage() ?: 'No se pudo importar el CSV.');
        return 1;
    }

    $this->info('Importación finalizada');
    $this->line('---');
    $this->line('Creados: '.$result['created']);
    $this->line('Actualizados: '.$result['updated']);
    $this->line('Saltados: '.$result['skipped']);
    $this->line('Inválidos: '.$result['invalid']);
    $this->line('Modo: '.$result['mode']);

    return 0;
})->purpose('Importa prospectos (CSV legado) como leads en etapa Seguimiento');

Artisan::command('import:contadores {file? : Ruta al CSV de contadores} {--dry-run : No escribe en BD, solo valida}', function () {
    $fileArg = $this->argument('file');
    $file = is_string($fileArg) && trim($fileArg) !== '' ? trim($fileArg) : null;
    $dryRun = (bool) $this->option('dry-run');

    if (!$file) {
        $this->error('Debes indicar la ruta del CSV: php artisan import:contadores C:/ruta/archivo.csv');
        return 1;
    }

    if (!file_exists($file)) {
        $this->error('Archivo no encontrado: '.$file);
        return 1;
    }

    $handle = fopen($file, 'r');
    if ($handle === false) {
        $this->error('No se pudo abrir el archivo.');
        return 1;
    }

    $header = fgetcsv($handle);
    if ($header === false) {
        fclose($handle);
        $this->error('CSV vacío o inválido.');
        return 1;
    }

    $normalizeKey = function (string $s): string {
        $s = trim($s);
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $s);
        $s = str_replace(['º', '°'], ['o', 'o'], $s);
        $s = preg_replace('/[^\p{L}\p{N}]+/u', '', $s) ?? $s;
        return $s;
    };

    $headerKeys = array_map(fn ($h) => $normalizeKey((string) $h), $header);
    $indexByKey = [];
    foreach ($headerKeys as $i => $k) {
        if ($k === '') {
            continue;
        }
        // keep the first occurrence
        if (!array_key_exists($k, $indexByKey)) {
            $indexByKey[$k] = $i;
        }
    }

    $findIndex = function (array $variants) use ($normalizeKey, $indexByKey): ?int {
        foreach ($variants as $v) {
            $k = $normalizeKey((string) $v);
            if ($k !== '' && array_key_exists($k, $indexByKey)) {
                return $indexByKey[$k];
            }
        }
        return null;
    };

    $idx = [
        'nro' => $findIndex(['N°', 'Nº', 'No', 'Nro', 'Nro.', '#']),
        'comercio' => $findIndex(['Comercio(s)', 'Comercio', 'Comercios', 'Comercio (s)']),
        'nom_contador' => $findIndex(['Nombre Contador', 'Nombre contador', 'Contador', 'Nombre']),
        'titular_tlf' => $findIndex(['Nombre en Celular', 'Nombre en celular', 'Nombre Celular', 'Nombre en Cel']),
        'telefono' => $findIndex(['Teléfono', 'Telefono', 'Celular']),
        'link' => $findIndex(['Link', 'Enlace', 'Url', 'URL']),
        'usuario' => $findIndex(['Usuario', 'Correo', 'Email', 'E-mail']),
        'contrasena' => $findIndex(['Contraseña', 'Contrasena', 'Password', 'Clave']),
        'servidor' => $findIndex(['Servidor', 'Server']),
    ];

    $clean = function ($v): ?string {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);
        if ($s === '') {
            return null;
        }
        $upper = mb_strtoupper($s, 'UTF-8');
        if (in_array($upper, ['NO TIENE', 'SIN CONTRASENA', 'SIN CONTRASEÑA', 'N/A', 'NA'], true)) {
            return null;
        }
        return $s;
    };

    $logPath = storage_path('logs/import_contadores.log');
    $rowNumber = 1;
    $created = 0;
    $skipped = 0;
    $invalid = 0;

    while (($row = fgetcsv($handle)) !== false) {
        $rowNumber++;

        $get = function (?int $i) use ($row) {
            if ($i === null) {
                return null;
            }
            return array_key_exists($i, $row) ? $row[$i] : null;
        };

        $data = [
            'nro' => $clean($get($idx['nro'])),
            'comercio' => $clean($get($idx['comercio'])),
            'nom_contador' => $clean($get($idx['nom_contador'])),
            'titular_tlf' => $clean($get($idx['titular_tlf'])),
            'telefono' => $clean($get($idx['telefono'])),
            'link' => $clean($get($idx['link'])),
            'usuario' => $clean($get($idx['usuario'])),
            'contrasena' => $clean($get($idx['contrasena'])),
            'servidor' => $clean($get($idx['servidor'])),
        ];

        // normalize usuario as lowercase when it looks like an email
        if (!empty($data['usuario']) && str_contains($data['usuario'], '@')) {
            $data['usuario'] = mb_strtolower($data['usuario'], 'UTF-8');
        }

        // skip empty-ish rows
        $hasAny = false;
        foreach (['nro', 'comercio', 'nom_contador', 'usuario', 'servidor'] as $k) {
            if (!empty($data[$k])) {
                $hasAny = true;
                break;
            }
        }
        if (!$hasAny) {
            $skipped++;
            continue;
        }

        if ($dryRun) {
            $this->line('[DRY] Row '.$rowNumber.' -> '.json_encode($data, JSON_UNESCAPED_UNICODE));
            continue;
        }

        try {
            Contador::query()->create(array_filter($data, fn ($v) => $v !== null && $v !== ''));
            $created++;
        } catch (\Throwable $e) {
            $invalid++;
            file_put_contents($logPath, 'Row '.$rowNumber.': '.$e->getMessage()."\n", FILE_APPEND);
        }
    }

    fclose($handle);

    $this->info('Importación finalizada');
    $this->line('---');
    $this->line('Creados: '.$created);
    $this->line('Saltados: '.$skipped);
    $this->line('Inválidos: '.$invalid);
    $this->line('Modo: '.($dryRun ? 'dry-run' : 'write'));
    if ($invalid > 0) {
        $this->line('Log: '.$logPath);
    }

    // Non-zero exit codes should be reserved for hard failures (missing/unreadable file).
    // Row-level errors are logged but should not make the HTTP endpoint return 422.
    return 0;
})->purpose('Importa contadores desde CSV legado');

Artisan::command('import:certificados {file? : Ruta al CSV de certificados} {--dry-run : No escribe en BD, solo valida}', function () {
    $fileArg = $this->argument('file');
    $file = is_string($fileArg) && trim($fileArg) !== '' ? trim($fileArg) : null;
    $dryRun = (bool) $this->option('dry-run');

    if (!$file) {
        $this->error('Debes indicar la ruta del CSV: php artisan import:certificados C:/ruta/archivo.csv');
        return 1;
    }
    if (!file_exists($file)) {
        $this->error('Archivo no encontrado: '.$file);
        return 1;
    }

    $handle = fopen($file, 'r');
    if ($handle === false) {
        $this->error('No se pudo abrir el archivo.');
        return 1;
    }

    $header = fgetcsv($handle);
    if ($header === false) {
        fclose($handle);
        $this->error('CSV vacío o inválido.');
        return 1;
    }

    $normalizeKey = function (string $s): string {
        $s = trim($s);
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $s);
        $s = preg_replace('/[^\p{L}\p{N}]+/u', '', $s) ?? $s;
        return $s;
    };

    $headerKeys = array_map(fn ($h) => $normalizeKey((string) $h), $header);
    $indexByKey = [];
    foreach ($headerKeys as $i => $k) {
        if ($k === '') continue;
        if (!array_key_exists($k, $indexByKey)) $indexByKey[$k] = $i;
    }

    $findIndex = function (array $variants) use ($normalizeKey, $indexByKey): ?int {
        foreach ($variants as $v) {
            $k = $normalizeKey((string) $v);
            if ($k !== '' && array_key_exists($k, $indexByKey)) return $indexByKey[$k];
        }
        return null;
    };

    $idx = [
        'nombre' => $findIndex(['nombre', 'Nombre']),
        'ruc' => $findIndex(['ruc', 'RUC']),
        'usuario' => $findIndex(['usuario', 'Usuario']),
        'clave' => $findIndex(['clave', 'Clave', 'password', 'contraseña', 'contrasena']),
        'fecha_creacion' => $findIndex(['fecha_creacion', 'fecha creacion', 'fechacreacion']),
        'fecha_vencimiento' => $findIndex(['fecha_vencimiento', 'fecha vencimiento', 'fechavencimiento']),
        'estado' => $findIndex(['estado', 'Estado']),
        'tipo' => $findIndex(['tipo', 'Tipo']),
        'observacion' => $findIndex(['observacion', 'observación', 'Observacion']),
        'ultima_notificacion' => $findIndex(['ultima_notificacion', 'ultima notificacion', 'ultimanotificacion']),
        'created_by' => $findIndex(['creado_por', 'created_by', 'creadopor']),
        'created_at' => $findIndex(['creado_en', 'created_at', 'creadoen']),
        'updated_by' => $findIndex(['actualizado_por', 'updated_by', 'actualizadopor']),
        'updated_at' => $findIndex(['actualizado_en', 'updated_at', 'actualizadoen']),
        // NOTE: imagen no viene en tu CSV ejemplo; se manejará por import separado.
    ];

    $clean = function ($v): ?string {
        if ($v === null) return null;
        $s = trim((string) $v);
        return $s === '' ? null : $s;
    };

    $parseDate = function (?string $s): ?string {
        $s = $s ? trim($s) : '';
        if ($s === '') return null;
        try {
            return \Illuminate\Support\Carbon::parse($s)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    };

    $parseDateTime = function (?string $s): ?string {
        $s = $s ? trim($s) : '';
        if ($s === '') return null;
        try {
            return \Illuminate\Support\Carbon::parse($s)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    };

    $logPath = storage_path('logs/import_certificados.log');
    $rowNumber = 1;
    $created = 0;
    $updated = 0;
    $skipped = 0;
    $invalid = 0;

    while (($row = fgetcsv($handle)) !== false) {
        $rowNumber++;

        $get = function (?int $i) use ($row) {
            if ($i === null) return null;
            return array_key_exists($i, $row) ? $row[$i] : null;
        };

        $nombre = $clean($get($idx['nombre']));
        $ruc = $clean($get($idx['ruc']));
        $tipo = $clean($get($idx['tipo']));

        if (!$nombre) {
            $skipped++;
            continue;
        }

        $payload = [
            'nombre' => $nombre,
            'ruc' => $ruc,
            'usuario' => $clean($get($idx['usuario'])),
            'clave' => $clean($get($idx['clave'])),
            'fecha_creacion' => $parseDate($clean($get($idx['fecha_creacion']))),
            'fecha_vencimiento' => $parseDate($clean($get($idx['fecha_vencimiento']))),
            'estado' => $clean($get($idx['estado'])) ?? 'activo',
            'tipo' => $tipo,
            'observacion' => $clean($get($idx['observacion'])),
            'ultima_notificacion' => $parseDateTime($clean($get($idx['ultima_notificacion']))),
        ];

        $createdBy = $clean($get($idx['created_by']));
        $updatedBy = $clean($get($idx['updated_by']));
        if (is_numeric($createdBy)) {
            $id = (int) $createdBy;
            if (User::query()->whereKey($id)->exists()) {
                $payload['created_by'] = $id;
            }
        }
        if (is_numeric($updatedBy)) {
            $id = (int) $updatedBy;
            if (User::query()->whereKey($id)->exists()) {
                $payload['updated_by'] = $id;
            }
        }

        $createdAt = $parseDateTime($clean($get($idx['created_at'])));
        $updatedAt = $parseDateTime($clean($get($idx['updated_at'])));

        if ($dryRun) {
            $this->line('[DRY] Row '.$rowNumber.' -> '.json_encode($payload, JSON_UNESCAPED_UNICODE));
            continue;
        }

        try {
            // Avoid duplicates on repeated imports
            $match = [
                'nombre' => $nombre,
                'ruc' => $ruc,
                'tipo' => $tipo,
            ];

            $cert = Certificado::query()->firstOrNew($match);
            $isNew = !$cert->exists;
            $cert->fill($payload);

            // set timestamps if provided
            if ($createdAt || $updatedAt) {
                $cert->timestamps = false;
                if ($createdAt) $cert->setAttribute('created_at', $createdAt);
                if ($updatedAt) $cert->setAttribute('updated_at', $updatedAt);
            }

            $cert->save();
            $isNew ? $created++ : $updated++;
        } catch (\Throwable $e) {
            $invalid++;
            file_put_contents($logPath, 'Row '.$rowNumber.': '.$e->getMessage()."\n", FILE_APPEND);
        }
    }

    fclose($handle);

    $this->info('Importación finalizada');
    $this->line('---');
    $this->line('Creados: '.$created);
    $this->line('Actualizados: '.$updated);
    $this->line('Saltados: '.$skipped);
    $this->line('Inválidos: '.$invalid);
    $this->line('Modo: '.($dryRun ? 'dry-run' : 'write'));
    if ($invalid > 0) $this->line('Log: '.$logPath);

    return 0;
})->purpose('Importa certificados desde CSV (sin imágenes)');

Artisan::command('import:certificados:imagenes {file? : Ruta al ZIP con imágenes} {--dry-run : No escribe en BD, solo valida}', function () {
    $fileArg = $this->argument('file');
    $zipPath = is_string($fileArg) && trim($fileArg) !== '' ? trim($fileArg) : null;
    $dryRun = (bool) $this->option('dry-run');

    if (!$zipPath) {
        $this->error('Debes indicar la ruta del ZIP: php artisan import:certificados:imagenes C:/ruta/imagenes.zip');
        return 1;
    }
    if (!file_exists($zipPath)) {
        $this->error('Archivo no encontrado: '.$zipPath);
        return 1;
    }

    if (!class_exists(\ZipArchive::class)) {
        $this->error('ZipArchive no está disponible en esta instalación de PHP.');
        return 1;
    }

    $zip = new \ZipArchive();
    $open = $zip->open($zipPath);
    if ($open !== true) {
        $this->error('No se pudo abrir el ZIP.');
        return 1;
    }

    $tmpDir = storage_path('app/imports/tmp_certificados_'.uniqid());
    if (!is_dir($tmpDir) && !mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
        $zip->close();
        $this->error('No se pudo crear directorio temporal.');
        return 1;
    }

    $zip->extractTo($tmpDir);
    $zip->close();

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $updated = 0;
    $skipped = 0;
    $invalid = 0;
    $logPath = storage_path('logs/import_certificados_imagenes.log');

    $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpDir));
    foreach ($it as $fileInfo) {
        if (!$fileInfo->isFile()) continue;
        $ext = mb_strtolower($fileInfo->getExtension(), 'UTF-8');
        if (!in_array($ext, $allowed, true)) continue;

        $full = $fileInfo->getPathname();
        $base = $fileInfo->getBasename('.'.$fileInfo->getExtension());

        // Match RUC (11 digits) anywhere in filename
        if (!preg_match('/(\d{11})/', $base, $m)) {
            $skipped++;
            continue;
        }
        $ruc = $m[1];

        $cert = Certificado::query()->where('ruc', $ruc)->orderByDesc('id')->first();
        if (!$cert) {
            $skipped++;
            continue;
        }

        if (!empty($cert->imagen)) {
            $skipped++;
            continue;
        }

        $targetName = $ruc.'.'.$ext;
        $targetPath = 'certificados/'.$targetName;

        if ($dryRun) {
            $this->line('[DRY] '.$ruc.' -> '.$targetPath);
            continue;
        }

        try {
            Storage::disk('public')->putFileAs('certificados', new File($full), $targetName);
            $cert->imagen = $targetPath;
            $cert->save();
            $updated++;
        } catch (\Throwable $e) {
            $invalid++;
            file_put_contents($logPath, $full.': '.$e->getMessage()."\n", FILE_APPEND);
        }
    }

    // Best-effort cleanup
    try {
        $cleanup = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tmpDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($cleanup as $path) {
            $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
        rmdir($tmpDir);
    } catch (\Throwable $e) {
        // ignore
    }

    $this->info('Importación de imágenes finalizada');
    $this->line('---');
    $this->line('Actualizados: '.$updated);
    $this->line('Saltados: '.$skipped);
    $this->line('Inválidos: '.$invalid);
    $this->line('Modo: '.($dryRun ? 'dry-run' : 'write'));
    if ($invalid > 0) $this->line('Log: '.$logPath);

    return 0;
})->purpose('Importa imágenes de certificados desde ZIP (match por RUC en filename)');

Artisan::command('import:customers {file? : Ruta al CSV de clientes} {--dry-run : No escribe en BD, solo valida}', function () {
    $fileArg = $this->argument('file');
    $file = is_string($fileArg) && trim($fileArg) !== '' ? trim($fileArg) : null;
    $dryRun = (bool) $this->option('dry-run');

    if (!$file) {
        $this->error('Debes indicar la ruta del CSV: php artisan import:customers C:/ruta/clientes.csv');
        return 1;
    }
    if (!file_exists($file)) {
        $this->error('Archivo no encontrado: '.$file);
        return 1;
    }

    $firstLine = '';
    $fh = fopen($file, 'rb');
    if ($fh !== false) {
        $firstLine = (string) fgets($fh);
        fclose($fh);
    }
    $commaCount = substr_count($firstLine, ',');
    $semiCount = substr_count($firstLine, ';');
    $delimiter = $semiCount > $commaCount ? ';' : ',';

    $handle = fopen($file, 'r');
    if ($handle === false) {
        $this->error('No se pudo abrir el archivo.');
        return 1;
    }

    $header = fgetcsv($handle, 0, $delimiter);
    if ($header === false) {
        fclose($handle);
        $this->error('CSV vacío o inválido.');
        return 1;
    }

    $normalizeKey = function (string $s): string {
        $s = trim($s);
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $s);
        $s = str_replace(['º', '°'], ['o', 'o'], $s);
        $s = preg_replace('/[^\p{L}\p{N}]+/u', '', $s) ?? $s;
        return $s;
    };

    $headerKeys = array_map(fn ($h) => $normalizeKey((string) $h), $header);
    $indexByKey = [];
    foreach ($headerKeys as $i => $k) {
        if ($k === '') continue;
        if (!array_key_exists($k, $indexByKey)) $indexByKey[$k] = $i;
    }

    $findIndex = function (array $variants) use ($normalizeKey, $indexByKey): ?int {
        foreach ($variants as $v) {
            $k = $normalizeKey((string) $v);
            if ($k !== '' && array_key_exists($k, $indexByKey)) return $indexByKey[$k];
        }
        return null;
    };

    $idx = [
        'nombre' => $findIndex(['nombre', 'Nombre', 'cliente', 'nombre cliente', 'nombrecliente']),
        'empresa' => $findIndex(['empresa', 'Empresa', 'comercio', 'comercio(s)', 'comercios']),
        'documento' => $findIndex(['documento', 'Documento', 'doc', 'dni', 'ruc']),
        'telefono' => $findIndex(['telefono', 'Teléfono', 'celular', 'movil', 'telf']),
        'correo' => $findIndex(['correo', 'Correo', 'email', 'e-mail', 'usuario', 'mail']),
        'ciudad' => $findIndex(['ciudad', 'Ciudad']),
        'referencia' => $findIndex(['referencia', 'Referencia']),
        'fecha_contacto' => $findIndex(['fecha_contacto', 'fecha contacto', 'f. contacto', 'fcontacto']),
        'fecha_creacion' => $findIndex(['fecha_creacion', 'fecha creacion', 'created_at', 'creado_en', 'fechacreacion']),
    ];

    $clean = function ($v): ?string {
        if ($v === null) return null;
        $s = trim((string) $v);
        if ($s === '') return null;
        $upper = mb_strtoupper($s, 'UTF-8');
        if (in_array($upper, ['NO TIENE', 'N/A', 'NA', 'NULL'], true)) return null;
        return $s;
    };

    $digitsOnly = function ($value): ?string {
        $v = preg_replace('/\D+/', '', (string) $value);
        $v = is_string($v) ? trim($v) : '';
        if ($v === '') return null;

        $placeholders = ['55555555', '00000000', '12345678', '99999999', '11111111', '22222222'];
        if (in_array($v, $placeholders, true)) return null;

        return $v;
    };

    $inferDocumentType = function (?string $documentNumber): ?string {
        if (!$documentNumber) return null;
        $len = strlen($documentNumber);
        if ($len === 8) return 'dni';
        if ($len === 11) return 'ruc';
        return null;
    };

    $parseDateTime = function (?string $s): ?string {
        $s = $s ? trim($s) : '';
        if ($s === '') return null;
        try {
            return Carbon::parse($s)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    };

    $rowNumber = 1;
    $created = 0;
    $updated = 0;
    $skipped = 0;
    $invalid = 0;
    $logPath = storage_path('logs/import_customers.log');

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $rowNumber++;

        $get = function (?int $i) use ($row) {
            if ($i === null) return null;
            return array_key_exists($i, $row) ? $row[$i] : null;
        };

        $nombre = $clean($get($idx['nombre']));
        $empresa = $clean($get($idx['empresa']));
        $telefono = $clean($get($idx['telefono']));
        $correo = $clean($get($idx['correo']));
        $documento = $digitsOnly($get($idx['documento']));
        $docType = $inferDocumentType($documento);

        if (!$nombre && !$empresa && !$telefono && !$correo && !$documento) {
            $skipped++;
            continue;
        }

        if ($documento && !$docType) {
            // Documento con longitud no estándar
            $documento = null;
        }

        $ciudad = $clean($get($idx['ciudad']));
        $referencia = $clean($get($idx['referencia']));
        $addressParts = array_values(array_filter([
            $ciudad ? "Ciudad: {$ciudad}" : null,
            $referencia ? "Ref: {$referencia}" : null,
        ]));
        $companyAddress = $addressParts ? mb_substr(implode(' | ', $addressParts), 0, 255) : null;

        $createdAt = $parseDateTime($clean($get($idx['fecha_creacion']))) ?? now()->toDateTimeString();
        $contactAt = $parseDateTime($clean($get($idx['fecha_contacto'])));
        $updatedAt = $contactAt && $contactAt > $createdAt ? $contactAt : $createdAt;

        $payload = [
            'name' => $nombre ?? $empresa ?? ($telefono ? "Cliente {$telefono}" : 'Cliente'),
            'contact_name' => $nombre,
            'contact_phone' => $telefono,
            'contact_email' => $correo,
            'company_name' => $empresa,
            'company_address' => $companyAddress,
            'document_type' => $docType,
            'document_number' => $documento,
        ];

        if ($dryRun) {
            $this->line('[DRY] Row '.$rowNumber.' -> '.json_encode($payload, JSON_UNESCAPED_UNICODE));
            continue;
        }

        try {
            $customer = null;
            if ($docType && $documento) {
                $customer = Customer::query()->firstOrNew([
                    'document_type' => $docType,
                    'document_number' => $documento,
                ]);
            } else {
                $customer = new Customer();
            }

            $isNew = !$customer->exists;
            $customer->fill($payload);

            $customer->timestamps = false;
            $customer->setAttribute('created_at', $createdAt);
            $customer->setAttribute('updated_at', $updatedAt);
            $customer->save();

            $isNew ? $created++ : $updated++;
        } catch (\Throwable $e) {
            $invalid++;
            file_put_contents($logPath, 'Row '.$rowNumber.': '.$e->getMessage()."\n", FILE_APPEND);
        }
    }

    fclose($handle);

    $this->info('Importación finalizada');
    $this->line('---');
    $this->line('Creados: '.$created);
    $this->line('Actualizados: '.$updated);
    $this->line('Saltados: '.$skipped);
    $this->line('Inválidos: '.$invalid);
    $this->line('Modo: '.($dryRun ? 'dry-run' : 'write'));
    if ($invalid > 0) $this->line('Log: '.$logPath);

    return 0;
})->purpose('Importa clientes desde CSV legado');

Artisan::command('import:incidencias {file? : Ruta al CSV de incidencias} {--dry-run : No escribe en BD, solo valida}', function () {
    $fileArg = $this->argument('file');
    $file = is_string($fileArg) && trim($fileArg) !== '' ? trim($fileArg) : null;
    $dryRun = (bool) $this->option('dry-run');

    if (!$file) {
        $this->error('Debes indicar la ruta del CSV: php artisan import:incidencias C:/ruta/incidencias.csv');
        return 1;
    }
    if (!file_exists($file)) {
        $this->error('Archivo no encontrado: '.$file);
        return 1;
    }

    $firstLine = '';
    $fh = fopen($file, 'rb');
    if ($fh !== false) {
        $firstLine = (string) fgets($fh);
        fclose($fh);
    }
    $commaCount = substr_count($firstLine, ',');
    $semiCount = substr_count($firstLine, ';');
    $delimiter = $semiCount > $commaCount ? ';' : ',';

    $handle = fopen($file, 'r');
    if ($handle === false) {
        $this->error('No se pudo abrir el archivo.');
        return 1;
    }

    $header = fgetcsv($handle, 0, $delimiter);
    if ($header === false) {
        fclose($handle);
        $this->error('CSV vacío o inválido.');
        return 1;
    }

    $normalizeKey = function (string $s): string {
        $s = trim($s);
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $s);
        $s = str_replace(['º', '°'], ['o', 'o'], $s);
        $s = preg_replace('/[^\p{L}\p{N}]+/u', '', $s) ?? $s;
        return $s;
    };

    $headerKeys = array_map(fn ($h) => $normalizeKey((string) $h), $header);
    $indexByKey = [];
    foreach ($headerKeys as $i => $k) {
        if ($k === '') continue;
        if (!array_key_exists($k, $indexByKey)) $indexByKey[$k] = $i;
    }

    $findIndex = function (array $variants) use ($normalizeKey, $indexByKey): ?int {
        foreach ($variants as $v) {
            $k = $normalizeKey((string) $v);
            if ($k !== '' && array_key_exists($k, $indexByKey)) return $indexByKey[$k];
        }
        return null;
    };

    $idx = [
        'correlativo' => $findIndex(['correlativo', 'correlative', 'codigo', 'código']),
        'nombre' => $findIndex([
            'nombre_incidencia',
            'nombreincidencia',
            'nombre de la incidencia',
            'nombredelaincidencia',
            'titulo',
            'título',
            'title',
            'nombre'
        ]),
        'cliente_id' => $findIndex(['cliente_id', 'clienteid', 'customer_id', 'customerid', 'nombre del cliente', 'nombredelcliente', 'cliente']),
        'usuario_id' => $findIndex(['usuario_id', 'usuarioid', 'created_by', 'creado_por']),
        'fecha' => $findIndex(['fecha', 'date']),
        'prioridad' => $findIndex(['prioridad', 'priority']),
        'observaciones' => $findIndex(['observaciones', 'observacion', 'notes', 'nota']),
        'fecha_creacion' => $findIndex(['fecha_creacion', 'fecha creacion', 'created_at', 'creado_en']),
        'columna' => $findIndex(['columna_backlog', 'columna', 'estado', 'stage', 'etapa']),
    ];

    $clean = function ($v): ?string {
        if ($v === null) return null;
        $s = trim((string) $v);
        if ($s === '') return null;
        $upper = mb_strtoupper($s, 'UTF-8');
        if (in_array($upper, ['NO TIENE', 'N/A', 'NA', 'NULL'], true)) return null;
        return $s;
    };

    $parseDate = function (?string $s): ?string {
        $s = $s ? trim($s) : '';
        if ($s === '') return null;
        try {
            return Carbon::parse($s)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    };

    $parseDateTime = function (?string $s): ?string {
        $s = $s ? trim($s) : '';
        if ($s === '') return null;
        try {
            return Carbon::parse($s)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    };

    $normalizeStage = function (?string $s): string {
        $s = $s ? trim($s) : '';
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $s);
        $s = preg_replace('/[^a-z0-9]+/', '_', $s) ?? $s;
        $s = trim($s, '_');
        return $s;
    };

    $stageIdByKey = IncidenceStage::query()->pluck('id', 'key')->all();
    $fallbackStageId = (int) IncidenceStage::query()->orderBy('sort_order')->orderBy('id')->value('id');

    $mapStageId = function (?string $raw) use ($normalizeStage, $stageIdByKey, $fallbackStageId): int {
        $key = $normalizeStage($raw);
        $syn = [
            'nuevo' => 'nuevo',
            'nueva' => 'nuevo',
            'pendiente' => 'nuevo',
            'por_hacer' => 'nuevo',
            'en_proceso' => 'en_proceso',
            'proceso' => 'en_proceso',
            'enproceso' => 'en_proceso',
            'resuelto' => 'resuelto',
            'cerrado' => 'resuelto',
            'finalizado' => 'resuelto',
            'terminado' => 'resuelto',
        ];

        $mappedKey = $syn[$key] ?? null;
        if ($mappedKey && isset($stageIdByKey[$mappedKey])) return (int) $stageIdByKey[$mappedKey];
        if ($key !== '' && isset($stageIdByKey[$key])) return (int) $stageIdByKey[$key];
        return $fallbackStageId ?: (int) ($stageIdByKey['nuevo'] ?? array_values($stageIdByKey)[0] ?? 0);
    };

    if (!$fallbackStageId && count($stageIdByKey) === 0) {
        fclose($handle);
        $this->error('No hay etapas de incidencias en la BD. Ejecuta seeders primero.');
        return 1;
    }

    $rowNumber = 1;
    $created = 0;
    $updated = 0;
    $skipped = 0;
    $invalid = 0;
    $logPath = storage_path('logs/import_incidencias.log');

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $rowNumber++;

        $get = function (?int $i) use ($row) {
            if ($i === null) return null;
            return array_key_exists($i, $row) ? $row[$i] : null;
        };

        $title = $clean($get($idx['nombre']));
        if (!$title) {
            $skipped++;
            continue;
        }

        $correlative = $clean($get($idx['correlativo']));
        $customerId = $clean($get($idx['cliente_id']));
        $createdBy = $clean($get($idx['usuario_id']));
        $priorityRaw = mb_strtolower((string) ($clean($get($idx['prioridad'])) ?? ''), 'UTF-8');
        $priority = 'media';
        if (in_array($priorityRaw, ['alta', 'high', 'urgente'], true)) $priority = 'alta';
        if (in_array($priorityRaw, ['baja', 'low'], true)) $priority = 'baja';

        $payload = [
            'correlative' => $correlative,
            'stage_id' => $mapStageId($clean($get($idx['columna']))),
            'customer_id' => null,
            'created_by' => null,
            'title' => $title,
            'date' => $parseDate($clean($get($idx['fecha']))),
            'priority' => $priority,
            'notes' => $clean($get($idx['observaciones'])),
            'archived_at' => null,
        ];

        if (is_numeric($customerId)) {
            $cid = (int) $customerId;
            if (Customer::query()->whereKey($cid)->exists()) $payload['customer_id'] = $cid;
        } elseif (is_string($customerId) && trim($customerId) !== '') {
            $customerName = trim($customerId);
            $customer = Customer::query()
                ->where('name', $customerName)
                ->orWhere('company_name', $customerName)
                ->first();

            if (!$customer) {
                $customer = Customer::query()
                    ->where('name', 'like', '%'.$customerName.'%')
                    ->orWhere('company_name', 'like', '%'.$customerName.'%')
                    ->first();
            }

            if ($customer) {
                $payload['customer_id'] = $customer->id;
            }
        }
        if (is_numeric($createdBy)) {
            $uid = (int) $createdBy;
            if (User::query()->whereKey($uid)->exists()) $payload['created_by'] = $uid;
        }

        $createdAt = $parseDateTime($clean($get($idx['fecha_creacion']))) ?? now()->toDateTimeString();
        $updatedAt = $createdAt;

        if ($dryRun) {
            $this->line('[DRY] Row '.$rowNumber.' -> '.json_encode($payload, JSON_UNESCAPED_UNICODE));
            continue;
        }

        try {
            $incidence = null;
            if ($correlative) {
                $incidence = Incidence::query()->firstOrNew(['correlative' => $correlative]);
            } else {
                $incidence = new Incidence();
            }

            $isNew = !$incidence->exists;
            $incidence->fill($payload);

            $incidence->timestamps = false;
            $incidence->setAttribute('created_at', $createdAt);
            $incidence->setAttribute('updated_at', $updatedAt);
            $incidence->save();

            if (!$correlative) {
                $incidence->correlative = 'INC-'.str_pad((string) $incidence->id, 6, '0', STR_PAD_LEFT);
                $incidence->save();
            }

            $isNew ? $created++ : $updated++;
        } catch (\Throwable $e) {
            $invalid++;
            file_put_contents($logPath, 'Row '.$rowNumber.': '.$e->getMessage()."\n", FILE_APPEND);
        }
    }

    fclose($handle);

    $this->info('Importación finalizada');
    $this->line('---');
    $this->line('Creados: '.$created);
    $this->line('Actualizados: '.$updated);
    $this->line('Saltados: '.$skipped);
    $this->line('Inválidos: '.$invalid);
    $this->line('Modo: '.($dryRun ? 'dry-run' : 'write'));
    if ($invalid > 0) $this->line('Log: '.$logPath);

    return 0;
})->purpose('Importa incidencias desde CSV legado');

Artisan::command('import:waiting-leads {file? : Ruta al CSV de leads en espera} {--dry-run : No escribe en BD, solo valida}', function () {
    $fileArg = $this->argument('file');
    $file = is_string($fileArg) && trim($fileArg) !== '' ? trim($fileArg) : null;
    $dryRun = (bool) $this->option('dry-run');

    if (!$file) {
        $this->error('Debes indicar la ruta del CSV: php artisan import:waiting-leads C:/ruta/espera.csv');
        return 1;
    }
    if (!file_exists($file)) {
        $this->error('Archivo no encontrado: '.$file);
        return 1;
    }

    $firstLine = '';
    $fh = fopen($file, 'rb');
    if ($fh !== false) {
        $firstLine = (string) fgets($fh);
        fclose($fh);
    }
    $commaCount = substr_count($firstLine, ',');
    $semiCount = substr_count($firstLine, ';');
    $delimiter = $semiCount > $commaCount ? ';' : ',';

    $handle = fopen($file, 'r');
    if ($handle === false) {
        $this->error('No se pudo abrir el archivo.');
        return 1;
    }

    $header = fgetcsv($handle, 0, $delimiter);
    if ($header === false) {
        fclose($handle);
        $this->error('CSV vacío o inválido.');
        return 1;
    }

    $normalizeKey = function (string $s): string {
        $s = trim($s);
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $s);
        $s = preg_replace('/[^\p{L}\p{N}]+/u', '', $s) ?? $s;
        return $s;
    };

    $headerKeys = array_map(fn ($h) => $normalizeKey((string) $h), $header);
    $indexByKey = [];
    foreach ($headerKeys as $i => $k) {
        if ($k === '') continue;
        if (!array_key_exists($k, $indexByKey)) $indexByKey[$k] = $i;
    }

    $findIndex = function (array $variants) use ($normalizeKey, $indexByKey): ?int {
        foreach ($variants as $v) {
            $k = $normalizeKey((string) $v);
            if ($k !== '' && array_key_exists($k, $indexByKey)) return $indexByKey[$k];
        }
        return null;
    };

    $idx = [
        'nombre' => $findIndex(['nombre', 'name']),
        'empresa' => $findIndex(['empresa', 'company', 'company_name']),
        'telefono' => $findIndex(['telefono', 'celular', 'phone']),
        'correo' => $findIndex(['correo', 'email']),
        'documento' => $findIndex(['documento', 'doc', 'dni', 'ruc', 'document_number']),
        'observacion' => $findIndex(['observacion', 'observación', 'notes', 'nota']),
        'created_by' => $findIndex(['created_by', 'creado_por', 'usuario_id']),
        'created_at' => $findIndex(['created_at', 'fecha_creacion', 'creado_en']),
    ];

    $clean = function ($v): ?string {
        if ($v === null) return null;
        $s = trim((string) $v);
        return $s === '' ? null : $s;
    };

    $digitsOnly = function ($value): ?string {
        $v = preg_replace('/\D+/', '', (string) $value);
        $v = is_string($v) ? trim($v) : '';
        return $v === '' ? null : $v;
    };

    $inferDocumentType = function (?string $documentNumber): ?string {
        if (!$documentNumber) return null;
        $len = strlen($documentNumber);
        if ($len === 8) return 'dni';
        if ($len === 11) return 'ruc';
        return null;
    };

    $parseDateTime = function (?string $s): ?string {
        $s = $s ? trim($s) : '';
        if ($s === '') return null;
        try {
            return Carbon::parse($s)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    };

    $rowNumber = 1;
    $created = 0;
    $skipped = 0;
    $invalid = 0;
    $logPath = storage_path('logs/import_waiting_leads.log');

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $rowNumber++;

        $get = function (?int $i) use ($row) {
            if ($i === null) return null;
            return array_key_exists($i, $row) ? $row[$i] : null;
        };

        $nombre = $clean($get($idx['nombre']));
        $empresa = $clean($get($idx['empresa']));
        $telefono = $clean($get($idx['telefono']));
        $correo = $clean($get($idx['correo']));
        $documento = $digitsOnly($get($idx['documento']));
        $docType = $inferDocumentType($documento);

        if (!$nombre && !$empresa && !$telefono && !$correo && !$documento) {
            $skipped++;
            continue;
        }

        if ($documento && !$docType) {
            $documento = null;
        }

        $leadId = null;
        if ($docType && $documento) {
            $leadId = Lead::query()
                ->where('document_type', $docType)
                ->where('document_number', $documento)
                ->orderByDesc('id')
                ->value('id');
        }

        if ($leadId && WaitingLead::query()->where('lead_id', $leadId)->exists()) {
            $skipped++;
            continue;
        }

        $payload = [
            'lead_id' => $leadId,
            'name' => $nombre ?? $empresa,
            'contact_name' => $nombre,
            'contact_phone' => $telefono,
            'contact_email' => $correo,
            'company_name' => $empresa,
            'company_address' => null,
            'document_type' => $docType,
            'document_number' => $documento,
            'observacion' => $clean($get($idx['observacion'])),
            'created_by' => null,
        ];

        $createdBy = $clean($get($idx['created_by']));
        if (is_numeric($createdBy)) {
            $uid = (int) $createdBy;
            if (User::query()->whereKey($uid)->exists()) $payload['created_by'] = $uid;
        }

        $createdAt = $parseDateTime($clean($get($idx['created_at']))) ?? now()->toDateTimeString();
        $updatedAt = $createdAt;

        if ($dryRun) {
            $this->line('[DRY] Row '.$rowNumber.' -> '.json_encode($payload, JSON_UNESCAPED_UNICODE));
            continue;
        }

        try {
            $waiting = new WaitingLead();
            $waiting->fill($payload);
            $waiting->timestamps = false;
            $waiting->setAttribute('created_at', $createdAt);
            $waiting->setAttribute('updated_at', $updatedAt);
            $waiting->save();
            $created++;
        } catch (\Throwable $e) {
            $invalid++;
            file_put_contents($logPath, 'Row '.$rowNumber.': '.$e->getMessage()."\n", FILE_APPEND);
        }
    }

    fclose($handle);

    $this->info('Importación finalizada');
    $this->line('---');
    $this->line('Creados: '.$created);
    $this->line('Saltados: '.$skipped);
    $this->line('Inválidos: '.$invalid);
    $this->line('Modo: '.($dryRun ? 'dry-run' : 'write'));
    if ($invalid > 0) $this->line('Log: '.$logPath);

    return 0;
})->purpose('Importa leads en espera desde CSV legado');

Artisan::command('import:lost-leads {file? : Ruta al CSV de leads desistidos} {--dry-run : No escribe en BD, solo valida}', function () {
    $fileArg = $this->argument('file');
    $file = is_string($fileArg) && trim($fileArg) !== '' ? trim($fileArg) : null;
    $dryRun = (bool) $this->option('dry-run');

    if (!$file) {
        $this->error('Debes indicar la ruta del CSV: php artisan import:lost-leads C:/ruta/desistidos.csv');
        return 1;
    }
    if (!file_exists($file)) {
        $this->error('Archivo no encontrado: '.$file);
        return 1;
    }

    $firstLine = '';
    $fh = fopen($file, 'rb');
    if ($fh !== false) {
        $firstLine = (string) fgets($fh);
        fclose($fh);
    }
    $commaCount = substr_count($firstLine, ',');
    $semiCount = substr_count($firstLine, ';');
    $delimiter = $semiCount > $commaCount ? ';' : ',';

    $handle = fopen($file, 'r');
    if ($handle === false) {
        $this->error('No se pudo abrir el archivo.');
        return 1;
    }

    $header = fgetcsv($handle, 0, $delimiter);
    if ($header === false) {
        fclose($handle);
        $this->error('CSV vacío o inválido.');
        return 1;
    }

    $normalizeKey = function (string $s): string {
        $s = trim($s);
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $s);
        $s = preg_replace('/[^\p{L}\p{N}]+/u', '', $s) ?? $s;
        return $s;
    };

    $headerKeys = array_map(fn ($h) => $normalizeKey((string) $h), $header);
    $indexByKey = [];
    foreach ($headerKeys as $i => $k) {
        if ($k === '') continue;
        if (!array_key_exists($k, $indexByKey)) $indexByKey[$k] = $i;
    }

    $findIndex = function (array $variants) use ($normalizeKey, $indexByKey): ?int {
        foreach ($variants as $v) {
            $k = $normalizeKey((string) $v);
            if ($k !== '' && array_key_exists($k, $indexByKey)) return $indexByKey[$k];
        }
        return null;
    };

    $idx = [
        'nombre' => $findIndex(['nombre', 'name']),
        'empresa' => $findIndex(['empresa', 'company', 'company_name']),
        'telefono' => $findIndex(['telefono', 'celular', 'phone']),
        'correo' => $findIndex(['correo', 'email']),
        'documento' => $findIndex(['documento', 'doc', 'dni', 'ruc', 'document_number']),
        'observacion' => $findIndex(['observacion', 'observación', 'notes', 'nota']),
        'created_by' => $findIndex(['created_by', 'creado_por', 'usuario_id']),
        'created_at' => $findIndex(['created_at', 'fecha_creacion', 'creado_en']),
    ];

    $clean = function ($v): ?string {
        if ($v === null) return null;
        $s = trim((string) $v);
        return $s === '' ? null : $s;
    };

    $digitsOnly = function ($value): ?string {
        $v = preg_replace('/\D+/', '', (string) $value);
        $v = is_string($v) ? trim($v) : '';
        return $v === '' ? null : $v;
    };

    $inferDocumentType = function (?string $documentNumber): ?string {
        if (!$documentNumber) return null;
        $len = strlen($documentNumber);
        if ($len === 8) return 'dni';
        if ($len === 11) return 'ruc';
        return null;
    };

    $parseDateTime = function (?string $s): ?string {
        $s = $s ? trim($s) : '';
        if ($s === '') return null;
        try {
            return Carbon::parse($s)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    };

    $rowNumber = 1;
    $created = 0;
    $skipped = 0;
    $invalid = 0;
    $logPath = storage_path('logs/import_lost_leads.log');

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $rowNumber++;

        $get = function (?int $i) use ($row) {
            if ($i === null) return null;
            return array_key_exists($i, $row) ? $row[$i] : null;
        };

        $nombre = $clean($get($idx['nombre']));
        $empresa = $clean($get($idx['empresa']));
        $telefono = $clean($get($idx['telefono']));
        $correo = $clean($get($idx['correo']));
        $documento = $digitsOnly($get($idx['documento']));
        $docType = $inferDocumentType($documento);

        if (!$nombre && !$empresa && !$telefono && !$correo && !$documento) {
            $skipped++;
            continue;
        }

        if ($documento && !$docType) {
            $documento = null;
        }

        $leadId = null;
        if ($docType && $documento) {
            $leadId = Lead::query()
                ->where('document_type', $docType)
                ->where('document_number', $documento)
                ->orderByDesc('id')
                ->value('id');
        }

        if ($leadId && LostLead::query()->where('lead_id', $leadId)->exists()) {
            $skipped++;
            continue;
        }

        $payload = [
            'lead_id' => $leadId,
            'name' => $nombre ?? $empresa,
            'contact_name' => $nombre,
            'contact_phone' => $telefono,
            'contact_email' => $correo,
            'company_name' => $empresa,
            'company_address' => null,
            'document_type' => $docType,
            'document_number' => $documento,
            'observacion' => $clean($get($idx['observacion'])),
            'created_by' => null,
        ];

        $createdBy = $clean($get($idx['created_by']));
        if (is_numeric($createdBy)) {
            $uid = (int) $createdBy;
            if (User::query()->whereKey($uid)->exists()) $payload['created_by'] = $uid;
        }

        $createdAt = $parseDateTime($clean($get($idx['created_at']))) ?? now()->toDateTimeString();
        $updatedAt = $createdAt;

        if ($dryRun) {
            $this->line('[DRY] Row '.$rowNumber.' -> '.json_encode($payload, JSON_UNESCAPED_UNICODE));
            continue;
        }

        try {
            $lost = new LostLead();
            $lost->fill($payload);
            $lost->timestamps = false;
            $lost->setAttribute('created_at', $createdAt);
            $lost->setAttribute('updated_at', $updatedAt);
            $lost->save();
            $created++;
        } catch (\Throwable $e) {
            $invalid++;
            file_put_contents($logPath, 'Row '.$rowNumber.': '.$e->getMessage()."\n", FILE_APPEND);
        }
    }

    fclose($handle);

    $this->info('Importación finalizada');
    $this->line('---');
    $this->line('Creados: '.$created);
    $this->line('Saltados: '.$skipped);
    $this->line('Inválidos: '.$invalid);
    $this->line('Modo: '.($dryRun ? 'dry-run' : 'write'));
    if ($invalid > 0) $this->line('Log: '.$logPath);

    return 0;
})->purpose('Importa leads desistidos desde CSV legado');
