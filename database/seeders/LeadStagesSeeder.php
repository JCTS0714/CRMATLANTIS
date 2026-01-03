<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadStagesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $stages = [
            ['key' => 'follow_up', 'name' => 'Seguimiento', 'sort_order' => 10, 'is_won' => false],
            ['key' => 'qualified', 'name' => 'Calificado', 'sort_order' => 20, 'is_won' => false],
            ['key' => 'proposed', 'name' => 'Propuesto', 'sort_order' => 30, 'is_won' => false],
            ['key' => 'won', 'name' => 'Ganado', 'sort_order' => 40, 'is_won' => true],
        ];

        foreach ($stages as $stage) {
            DB::table('lead_stages')->updateOrInsert(
                ['key' => $stage['key']],
                array_merge($stage, ['updated_at' => $now, 'created_at' => $now])
            );
        }
    }
}
