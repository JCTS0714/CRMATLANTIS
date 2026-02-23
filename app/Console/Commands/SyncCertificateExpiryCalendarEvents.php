<?php

namespace App\Console\Commands;

use App\Models\Certificado;
use App\Services\Calendar\CalendarCertificateSyncService;
use Illuminate\Console\Command;

class SyncCertificateExpiryCalendarEvents extends Command
{
    protected $signature = 'calendar:sync-certificate-expiries {--assign-to= : User ID fallback when certificate has no creator/updater}';
    protected $description = 'Create or update calendar expiry events for existing certificates with expiration date.';

    public function __construct(private readonly CalendarCertificateSyncService $syncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $assignFallback = $this->option('assign-to');
        $assignFallback = is_numeric($assignFallback) ? (int) $assignFallback : null;

        $created = 0;
        $updated = 0;
        $skipped = 0;

        Certificado::query()
            ->whereNotNull('fecha_vencimiento')
            ->orderBy('id')
            ->chunkById(200, function ($certificados) use (&$created, &$updated, &$skipped, $assignFallback) {
                foreach ($certificados as $certificado) {
                    $assignedTo = $certificado->created_by
                        ? (int) $certificado->created_by
                        : ($certificado->updated_by ? (int) $certificado->updated_by : $assignFallback);

                    if (!$assignedTo) {
                        $skipped++;
                        continue;
                    }

                    $result = $this->syncService->syncCertificateExpiryEvent($certificado, $assignedTo);
                    if (($result['created'] ?? false) === true) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }
            });

        $this->info("Certificate expiry events synced. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
