<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Contoh Data DELAYED (Merah)
        Project::create([
            'project_code' => '2601.GOS-002',
            'project_name' => 'SKID MULTISTAGE WATER PUMP',
            'customer'     => 'GOS',
            'due_date'     => '2026-02-06',
            'plan_progress'   => 100,
            'actual_progress' => 70, // Gap -30%
        ]);

        // 2. Contoh Data AT RISK (Kuning)
        Project::create([
            'project_code' => '2507.PEP-040',
            'project_name' => 'REFURBISHMENT FOR UTILITY - SWO',
            'customer'     => 'PEP',
            'due_date'     => '2025-09-12',
            'plan_progress'   => 100,
            'actual_progress' => 90, // Gap -10%
        ]);

        // 3. Contoh Data ON TRACK (Hijau)
        Project::create([
            'project_code' => '2603.LCI-017',
            'project_name' => 'FUEL TANK 6 KL',
            'customer'     => 'LCI',
            'due_date'     => '2026-04-27',
            'plan_progress'   => 90,
            'actual_progress' => 90, // Gap 0%
        ]);
    }
}
