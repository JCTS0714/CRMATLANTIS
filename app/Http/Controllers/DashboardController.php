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

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $canLeads = $user?->can('leads.view') === true;
        $canCustomers = $user?->can('customers.view') === true;
        $canIncidencias = $user?->can('incidencias.view') === true;
        $canContadores = $user?->can('contadores.view') === true;
        $canCertificados = $user?->can('certificados.view') === true;
        $canCalendar = $user?->can('calendar.view') === true;

        $today = now()->startOfDay();
        $in7 = now()->addDays(7)->endOfDay();
        $in30 = now()->addDays(30)->endOfDay();

        $cards = [];
        $lists = [];

        if ($canLeads) {
            $cards['leads'] = [
                'total' => Lead::query()->count(),
                'open' => Lead::query()->whereNull('archived_at')->count(),
                'archived' => Lead::query()->whereNotNull('archived_at')->count(),
            ];

            $lists['recent_leads'] = Lead::query()
                ->select(['id', 'name', 'company_name', 'amount', 'currency', 'created_at'])
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        if ($canCustomers) {
            $cards['customers'] = [
                'total' => Customer::query()->count(),
            ];
        }

        if ($canIncidencias) {
            $cards['incidencias'] = [
                'open' => Incidence::query()->whereNull('archived_at')->count(),
                'archived' => Incidence::query()->whereNotNull('archived_at')->count(),
            ];

            $lists['recent_incidences'] = Incidence::query()
                ->select(['id', 'correlative', 'title', 'priority', 'date', 'created_at'])
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        if ($canContadores) {
            $cards['contadores'] = [
                'total' => Contador::query()->count(),
                'assigned' => Contador::query()->whereHas('customers')->count(),
                'unassigned' => Contador::query()->whereDoesntHave('customers')->count(),
            ];
        }

        if ($canCertificados) {
            $cards['certificados'] = [
                'total' => Certificado::query()->count(),
                'activos' => Certificado::query()->where('estado', 'activo')->count(),
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
            'cards' => $cards,
            'lists' => $lists,
        ]);
    }
}
