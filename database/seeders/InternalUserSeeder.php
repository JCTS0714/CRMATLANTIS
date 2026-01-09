<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class InternalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $name = (string) env('SEED_ADMIN_NAME', 'Administrador');
        $email = (string) env('SEED_ADMIN_EMAIL', 'admin@crmatlantis.local');

        $plainPasswordFromEnv = (string) env('SEED_ADMIN_PASSWORD', '');

        $user = User::query()->where('email', $email)->first();
        $generatedPassword = false;
        $plainPasswordToShow = '';

        if (!$user) {
            $plainPassword = $plainPasswordFromEnv;

            if ($plainPassword === '') {
                $plainPassword = Str::password(24);
                $generatedPassword = true;
            }

            $plainPasswordToShow = $plainPassword;

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($plainPassword),
            ]);
        } else {
            $user->name = $name;

            if ($plainPasswordFromEnv !== '') {
                $user->password = Hash::make($plainPasswordFromEnv);
                $plainPasswordToShow = $plainPasswordFromEnv;
            }

            $user->save();
        }

        // Ensure roles exist (RolesAndPermissionsSeeder should be run first)
        Role::findOrCreate('admin', $guard);
        $user->syncRoles(['admin']);

        if ($generatedPassword) {
            $this->command?->warn('Generated admin password (store it securely and change it after first login):');
            $this->command?->warn("Email: {$email}");
            $this->command?->warn("Password: {$plainPasswordToShow}");
            return;
        }

        if ($plainPasswordToShow !== '') {
            $this->command?->warn('Admin password was set from SEED_ADMIN_PASSWORD:');
            $this->command?->warn("Email: {$email}");
            $this->command?->warn("Password: {$plainPasswordToShow}");
            return;
        }

        $this->command?->info("Admin user ensured: {$email}");
    }
}
