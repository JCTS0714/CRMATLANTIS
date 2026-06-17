<?php

namespace App\Services\Lead;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStage;
use Illuminate\Support\Facades\DB;

class LeadCustomerConversionService
{
    public function convert(Lead $lead): Customer
    {
        if ($lead->archived_at) {
            throw new \RuntimeException('No se puede convertir un lead archivado.');
        }

        return DB::transaction(function () use ($lead) {
            $wonStage = LeadStage::query()->where('is_won', true)->first();
            $existingCustomer = null;

            if ($lead->document_type && $lead->document_number) {
                $existingCustomer = Customer::query()
                    ->where('document_type', $lead->document_type)
                    ->where('document_number', $lead->document_number)
                    ->first();
            }

            if ($existingCustomer) {
                if ($wonStage) {
                    $lead->stage_id = $wonStage->id;
                }
                $lead->customer_id = $existingCustomer->id;
                $lead->archived_at = now();
                $lead->save();

                return $existingCustomer;
            }

            $customer = Customer::create([
                'name' => $lead->name,
                'contact_name' => $lead->contact_name,
                'contact_phone' => $lead->contact_phone,
                'contact_email' => $lead->contact_email,
                'company_name' => $lead->company_name,
                'company_address' => $lead->company_address,
                'document_type' => $lead->document_type,
                'document_number' => $lead->document_number,
            ]);

            if ($wonStage) {
                $lead->stage_id = $wonStage->id;
            }

            $lead->customer_id = $customer->id;
            $lead->archived_at = now();
            $lead->save();

            return $customer;
        });
    }
}
