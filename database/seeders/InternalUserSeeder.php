<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class InternalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $user = User::updateOrCreate(
            ['email' => 'admin@crmatlantis.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Atlantis2025!'),
            ]
        );

        // Ensure roles exist (RolesAndPermissionsSeeder should be run first)
        Role::findOrCreate('admin', $guard);
        $user->syncRoles(['admin']);
    }
}
