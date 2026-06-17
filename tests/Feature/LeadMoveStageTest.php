<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeadMoveStageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('leads.update', 'web');
    }

    public function test_moving_a_lead_to_a_won_stage_creates_a_customer_and_archives_the_lead(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('leads.update');

        $initialStage = LeadStage::create([
            'key' => 'nuevo',
            'name' => 'Nuevo',
            'sort_order' => 1,
            'is_won' => false,
        ]);

        $wonStage = LeadStage::create([
            'key' => 'ganado',
            'name' => 'Ganado',
            'sort_order' => 2,
            'is_won' => true,
        ]);

        $lead = Lead::create([
            'stage_id' => $initialStage->id,
            'name' => 'Lead Atlantis',
            'contact_name' => 'Juan Perez',
            'contact_phone' => '999888777',
            'contact_email' => 'juan@example.com',
            'company_name' => 'Atlantis SAC',
            'company_address' => 'Av. Central 123',
            'document_type' => 'RUC',
            'document_number' => '20123456789',
            'position' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->patchJson("/leads/{$lead->id}/move-stage", [
                'stage_id' => $wonStage->id,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.stage_id', $wonStage->id);

        $lead->refresh();
        $customer = Customer::query()->where('document_number', '20123456789')->first();

        $this->assertNotNull($customer);
        $this->assertSame($customer->id, $lead->customer_id);
        $this->assertSame($wonStage->id, $lead->stage_id);
        $this->assertNotNull($lead->archived_at);
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_moving_a_lead_to_a_won_stage_reuses_an_existing_customer_with_the_same_document(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('leads.update');

        $initialStage = LeadStage::create([
            'key' => 'contactado',
            'name' => 'Contactado',
            'sort_order' => 1,
            'is_won' => false,
        ]);

        $wonStage = LeadStage::create([
            'key' => 'ganado',
            'name' => 'Ganado',
            'sort_order' => 2,
            'is_won' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Cliente Existente',
            'contact_name' => 'Ana Lopez',
            'contact_email' => 'ana@example.com',
            'document_type' => 'DNI',
            'document_number' => '12345678',
        ]);

        $lead = Lead::create([
            'stage_id' => $initialStage->id,
            'name' => 'Lead Reutilizado',
            'contact_name' => 'Ana Lopez',
            'contact_email' => 'ana@example.com',
            'document_type' => 'DNI',
            'document_number' => '12345678',
            'position' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->patchJson("/leads/{$lead->id}/move-stage", [
                'stage_id' => $wonStage->id,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.customer_id', $customer->id)
            ->assertJsonPath('data.stage_id', $wonStage->id);

        $lead->refresh();

        $this->assertSame($customer->id, $lead->customer_id);
        $this->assertSame($wonStage->id, $lead->stage_id);
        $this->assertNotNull($lead->archived_at);
        $this->assertDatabaseCount('customers', 1);
    }
}
