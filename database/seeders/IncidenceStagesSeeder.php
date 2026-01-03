<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncidenceStagesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Ajustable: columnas base para Postventa.
        $stages = [
            ['key' => 'nuevo', 'name' => 'Nuevo', 'sort_order' => 10, 'is_done' => false],
            ['key' => 'en_proceso', 'name' => 'En proceso', 'sort_order' => 20, 'is_done' => false],
            ['key' => 'resuelto', 'name' => 'Resuelto', 'sort_order' => 30, 'is_done' => true],
        ];

        foreach ($stages as $stage) {
            DB::table('incidence_stages')->updateOrInsert(
                ['key' => $stage['key']],
                array_merge($stage, ['updated_at' => $now, 'created_at' => $now])
            );
        }
    }
}
