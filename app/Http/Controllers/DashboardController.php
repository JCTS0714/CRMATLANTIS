<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Certificado;
use App\Models\Contador;
use App\Models\Customer;
use App\Models\Incidence;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $month = trim((string) $request->query('month', ''));
        $month = $month !== '' ? $month : null;
        $periodStart = null;
        $periodEnd = null;

        if ($month !== null) {
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                return response()->json([
                    'message' => 'Parámetro month inválido. Usa formato YYYY-MM.',
                ], 422);
            }

            try {
                $periodStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $periodEnd = $periodStart->copy()->endOfMonth();
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Parámetro month inválido.',
                ], 422);
            }
        }

        $canLeads = $user?->can('leads.view') === true;
        $canCustomers = $user?->can('customers.view') === true;
        $canIncidencias = $user?->can('incidencias.view') === true;
        $canContadores = $user?->can('contadores.view') === true;
        $canCertificados = $user?->can('certificados.view') === true;
        $canCalendar = $user?->can('calendar.view') === true;

        $base = $periodStart ? $periodStart->copy() : now();
        $today = $base->copy()->startOfDay();
        $in7 = $base->copy()->addDays(7)->endOfDay();
        $in30 = $base->copy()->addDays(30)->endOfDay();

        $cards = [];
        $lists = [];

        if ($canLeads) {
            $leadQuery = Lead::query();
            if ($periodStart && $periodEnd) {
                $leadQuery->whereBetween('created_at', [$periodStart, $periodEnd]);
            }

            $cards['leads'] = [
                'total' => (clone $leadQuery)->count(),
                'open' => (clone $leadQuery)->whereNull('archived_at')->count(),
                'archived' => (clone $leadQuery)->whereNotNull('archived_at')->count(),
            ];

            $recentLeads = Lead::query();
            if ($periodStart && $periodEnd) {
                $recentLeads->whereBetween('created_at', [$periodStart, $periodEnd]);
            }

            $lists['recent_leads'] = $recentLeads
                ->select(['id', 'name', 'company_name', 'amount', 'currency', 'created_at'])
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        if ($canCustomers) {
            $cards['customers'] = [
                'total' => Customer::query()
                    ->when($periodStart && $periodEnd, fn ($q) => $q->whereBetween('created_at', [$periodStart, $periodEnd]))
                    ->count(),
            ];
        }

        if ($canIncidencias) {
            $incQuery = Incidence::query();
            if ($periodStart && $periodEnd) {
                $incQuery->whereBetween('created_at', [$periodStart, $periodEnd]);
            }

            $cards['incidencias'] = [
                'open' => (clone $incQuery)->whereNull('archived_at')->count(),
                'archived' => (clone $incQuery)->whereNotNull('archived_at')->count(),
            ];

            $recentIncidences = Incidence::query();
            if ($periodStart && $periodEnd) {
                $recentIncidences->whereBetween('created_at', [$periodStart, $periodEnd]);
            }

            $lists['recent_incidences'] = $recentIncidences
                ->select(['id', 'correlative', 'title', 'priority', 'date', 'created_at'])
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        if ($canContadores) {
            $contQuery = Contador::query();
            if ($periodStart && $periodEnd) {
                $contQuery->whereBetween('created_at', [$periodStart, $periodEnd]);
            }

            $cards['contadores'] = [
                'total' => (clone $contQuery)->count(),
                'assigned' => (clone $contQuery)->whereHas('customers')->count(),
                'unassigned' => (clone $contQuery)->whereDoesntHave('customers')->count(),
            ];
        }

        if ($canCertificados) {
            $certBase = Certificado::query();
            if ($periodStart && $periodEnd) {
                $certBase->whereBetween('created_at', [$periodStart, $periodEnd]);
            }

            $cards['certificados'] = [
                'total' => (clone $certBase)->count(),
                'activos' => (clone $certBase)->where('estado', 'activo')->count(),
                'por_vencer_30d' => Certificado::query()
                    ->where('estado', 'activo')
                    ->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [$today->toDateString(), $in30->toDateString()])
                    ->count(),
                'vencidos' => Certificado::query()
                    ->whereNotNull('fecha_vencimiento')
                    ->where('fecha_vencimiento', '<', $today->toDateString())
                    ->count(),
            ];

            $lists['certificados_por_vencer'] = Certificado::query()
                ->select(['id', 'nombre', 'ruc', 'tipo', 'estado', 'fecha_vencimiento'])
                ->where('estado', 'activo')
                ->whereNotNull('fecha_vencimiento')
                ->whereBetween('fecha_vencimiento', [$today->toDateString(), $in30->toDateString()])
                ->orderBy('fecha_vencimiento')
                ->limit(5)
                ->get();
        }

        if ($canCalendar && $user) {
            $cards['calendar'] = [
                'upcoming_7d' => CalendarEvent::query()
                    ->where('assigned_to', $user->id)
                    ->whereBetween('start_at', [$today, $in7])
                    ->count(),
            ];

            $lists['upcoming_events'] = CalendarEvent::query()
                ->select(['id', 'title', 'start_at', 'end_at', 'all_day', 'location'])
                ->where('assigned_to', $user->id)
                ->where('start_at', '>=', $today)
                ->orderBy('start_at')
                ->limit(5)
                ->get();
        }

        return response()->json([
            'generated_at' => now()->toISOString(),
            'filters' => [
                'month' => $month,
                'period_start' => $periodStart?->toDateString(),
                'period_end' => $periodEnd?->toDateString(),
            ],
            'cards' => $cards,
            'lists' => $lists,
        ]);
    }
}
