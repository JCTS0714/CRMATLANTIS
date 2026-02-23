<?php

namespace App\Services\Calendar;

use App\Models\CalendarEvent;
use App\Models\Certificado;
use Illuminate\Support\Carbon;

class CalendarCertificateSyncService
{
    public function syncCertificateExpiryEvent(Certificado $certificado, ?int $assignedToUserId): array
    {
        if (!$assignedToUserId || !$certificado->fecha_vencimiento) {
            return [
                'created' => false,
                'event' => null,
            ];
        }

        $startAt = Carbon::parse($certificado->fecha_vencimiento)->startOfDay();

        $event = CalendarEvent::query()
            ->where('event_type', 'certificate_expiry')
            ->where('related_type', Certificado::class)
            ->where('related_id', $certificado->id)
            ->where('assigned_to', $assignedToUserId)
            ->first();

        $created = !$event;

        if (!$event) {
            $event = new CalendarEvent();
            $event->created_by = $assignedToUserId;
            $event->assigned_to = $assignedToUserId;
            $event->event_type = 'certificate_expiry';
            $event->related_type = Certificado::class;
            $event->related_id = $certificado->id;
        }

        $event->title = 'Vencimiento certificado: ' . $certificado->nombre;
        $event->description = $this->buildDescription($certificado);
        $event->location = null;
        $event->all_day = true;
        $event->start_at = $startAt;
        $event->end_at = null;
        $event->reminder_minutes = 1440;
        $event->reminder_at = (clone $startAt)->subMinutes(1440);
        $event->reminded_at = null;
        $event->meta = [
            'certificate_id' => $certificado->id,
            'certificate_name' => $certificado->nombre,
            'ruc' => $certificado->ruc,
            'tipo' => $certificado->tipo,
            'estado' => $certificado->estado,
            'fecha_vencimiento' => optional($certificado->fecha_vencimiento)->toDateString(),
        ];
        $event->save();

        return [
            'created' => $created,
            'event' => $event,
        ];
    }

    private function buildDescription(Certificado $certificado): string
    {
        $parts = [
            'RUC: ' . ($certificado->ruc ?: 'N/D'),
            'Usuario: ' . ($certificado->usuario ?: 'N/D'),
            'Tipo: ' . ($certificado->tipo ?: 'N/D'),
        ];

        if ($certificado->observacion) {
            $parts[] = 'Observación: ' . $certificado->observacion;
        }

        return implode(' | ', $parts);
    }
}
