<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_permissions_sync_creates_route_and_dynamic_module_permissions_and_syncs_admin_role(): void
    {
        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $this->artisan('permissions:sync')
            ->assertExitCode(0);

        $this->assertDatabaseHas('permissions', [
            'name' => 'leads.view',
            'guard_name' => 'web',
        ]);

        $this->assertDatabaseHas('permissions', [
            'name' => 'customers.create',
            'guard_name' => 'web',
        ]);

        $this->assertDatabaseHas('permissions', [
            'name' => 'menu.inbox',
            'guard_name' => 'web',
        ]);

        $this->assertDatabaseHas('permissions', [
            'name' => 'menu.postventa',
            'guard_name' => 'web',
        ]);

        $adminRole->refresh();

        $this->assertTrue($adminRole->hasPermissionTo('leads.view'));
        $this->assertTrue($adminRole->hasPermissionTo('customers.create'));
        $this->assertTrue($adminRole->hasPermissionTo('menu.inbox'));
    }

    public function test_customers_route_requires_customers_view_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/customers')
            ->assertForbidden();

        Permission::findOrCreate('customers.view', 'web');
        $user->givePermissionTo('customers.view');

        $this->actingAs($user)
            ->get('/customers')
            ->assertRedirect('/postventa/clientes');
    }
}
