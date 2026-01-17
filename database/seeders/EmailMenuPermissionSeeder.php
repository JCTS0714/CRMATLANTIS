<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class EmailMenuPermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Permission::firstOrCreate([
            'name' => 'menu.email',
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);
    }
}
