<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelatedLookupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:lead,customer'],
            'q' => ['nullable', 'string', 'max:100'],
            'id' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $type = $validated['type'];
        $id = $validated['id'] ?? null;
        $q = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 8);

        if ($type === 'lead' && !$request->user()->can('leads.view')) {
            abort(403);
        }
        if ($type === 'customer' && !$request->user()->can('customers.view')) {
            abort(403);
        }

        if ($id) {
            $item = $type === 'lead'
                ? Lead::query()
                    ->select(['id', 'name', 'company_name', 'document_type', 'document_number'])
                    ->find($id)
                : Customer::query()
                    ->select(['id', 'name', 'company_name', 'document_type', 'document_number'])
                    ->find($id);

            return response()->json([
                'data' => $item ? $this->mapItem($type, $item) : null,
            ]);
        }

        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        if ($type === 'lead') {
            $items = Lead::query()
                ->select(['id', 'name', 'company_name', 'contact_email', 'document_type', 'document_number', 'updated_at', 'archived_at'])
                ->whereNull('won_at')
                ->whereNull('archived_at')
                ->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('company_name', 'like', "%{$q}%")
                        ->orWhere('contact_email', 'like', "%{$q}%")
                        ->orWhere('document_number', 'like', "%{$q}%");
                })
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => $items->map(fn (Lead $l) => $this->mapItem('lead', $l))->values(),
            ]);
        }

        $items = Customer::query()
            ->select(['id', 'name', 'company_name', 'contact_email', 'document_type', 'document_number', 'updated_at'])
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('company_name', 'like', "%{$q}%")
                    ->orWhere('contact_email', 'like', "%{$q}%")
                    ->orWhere('document_number', 'like', "%{$q}%");
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $items->map(fn (Customer $c) => $this->mapItem('customer', $c))->values(),
        ]);
    }

    private function mapItem(string $type, Lead|Customer $model): array
    {
        $main = $model->company_name ?: $model->name;
        $secondary = $model->company_name ? $model->name : null;
        $doc = $model->document_number
            ? trim((string) ($model->document_type ? $model->document_type.' ' : '').$model->document_number)
            : null;

        $label = $main;
        if ($secondary && $secondary !== $main) {
            $label .= ' — '.$secondary;
        }
        if ($doc) {
            $label .= ' ('.$doc.')';
        }

        return [
            'type' => $type,
            'id' => (int) $model->id,
            'label' => $label,
        ];
    }
}
