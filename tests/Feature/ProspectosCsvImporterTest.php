<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\User;
use App\Services\Lead\Imports\ProspectosCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProspectosCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_importer_creates_leads_and_normalizes_placeholder_documents(): void
    {
        $stage = LeadStage::create([
            'key' => 'follow_up',
            'name' => 'Seguimiento',
            'sort_order' => 1,
            'is_won' => false,
        ]);

        $filePath = $this->makeCsv([
            ['Nombre', 'Telefono', 'Empresa', 'Documento', 'Observacion', 'Ciudad', 'Referencia', 'Fecha_Creacion'],
            ['Ana Perez', '987654321', 'Atlantis', '23456789', 'Primera llamada', 'Lima', 'Facebook', '2026-01-10 10:00:00'],
            ['Sin Documento', '999111222', 'Atlantis 2', '55555555', 'Sin doc real', 'Cusco', 'Tik Tok', '2026-01-11 11:00:00'],
        ]);

        $result = app(ProspectosCsvImporter::class)->import($filePath, [
            'createdBy' => null,
        ]);

        $this->assertSame([
            'created' => 2,
            'updated' => 0,
            'skipped' => 0,
            'invalid' => 0,
            'mode' => 'WRITE',
        ], $result);

        $this->assertDatabaseHas('leads', [
            'stage_id' => $stage->id,
            'name' => 'Ana Perez',
            'document_type' => 'dni',
            'document_number' => '23456789',
            'referencia' => 'FACEBOOK',
        ]);

        $this->assertDatabaseHas('leads', [
            'name' => 'Sin Documento',
            'document_type' => null,
            'document_number' => null,
            'referencia' => 'TIK TOK',
        ]);
    }

    public function test_importer_skips_existing_active_lead_by_document_when_update_is_disabled(): void
    {
        $stage = LeadStage::create([
            'key' => 'follow_up',
            'name' => 'Seguimiento',
            'sort_order' => 1,
            'is_won' => false,
        ]);

        $existingLead = Lead::create([
            'stage_id' => $stage->id,
            'name' => 'Lead Existente',
            'document_type' => 'dni',
            'document_number' => '87654321',
            'position' => 1,
        ]);

        $filePath = $this->makeCsv([
            ['Nombre', 'Telefono', 'Empresa', 'Documento'],
            ['Lead Nuevo Nombre', '911222333', 'Atlantis', '87654321'],
        ]);

        $result = app(ProspectosCsvImporter::class)->import($filePath, [
            'updateExisting' => false,
        ]);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['skipped']);

        $existingLead->refresh();
        $this->assertSame('Lead Existente', $existingLead->name);
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_leads_import_endpoint_requires_permission_and_imports_csv(): void
    {
        $stage = LeadStage::create([
            'key' => 'follow_up',
            'name' => 'Seguimiento',
            'sort_order' => 1,
            'is_won' => false,
        ]);

        Permission::findOrCreate('leads.create', 'web');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('prospectos.csv', implode("\n", [
            'Nombre,Telefono,Empresa,Documento,Referencia',
            'Mario,988777666,CRM Atlantis,20123456789,Instagram',
        ]));

        $this->actingAs($user)
            ->post('/leads/import', ['file' => $file])
            ->assertForbidden();

        $user->givePermissionTo('leads.create');

        $file = UploadedFile::fake()->createWithContent('prospectos.csv', implode("\n", [
            'Nombre,Telefono,Empresa,Documento,Referencia',
            'Mario,988777666,CRM Atlantis,20123456789,Instagram',
        ]));

        $this->actingAs($user)
            ->post('/leads/import', ['file' => $file])
            ->assertOk()
            ->assertJsonPath('data.created', 1)
            ->assertJsonPath('data.mode', 'WRITE');

        $this->assertDatabaseHas('leads', [
            'stage_id' => $stage->id,
            'created_by' => $user->id,
            'name' => 'Mario',
            'document_type' => 'ruc',
            'document_number' => '20123456789',
            'referencia' => 'INSTAGRAM',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('framework/testing/imports'));

        parent::tearDown();
    }

    /**
     * @param  list<list<string|null>>  $rows
     */
    private function makeCsv(array $rows): string
    {
        $directory = storage_path('framework/testing/imports');
        File::ensureDirectoryExists($directory);

        $path = $directory.'\\'.uniqid('prospectos_', true).'.csv';
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('No se pudo crear archivo CSV temporal.');
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }
}
