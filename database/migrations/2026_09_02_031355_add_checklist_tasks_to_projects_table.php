<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Engineering (Bobot 15%)
            $table->float('task_engineering')->default(0);

            // Procurement & Warehouse (Bobot 10%)
            $table->float('task_main_material')->default(0);
            $table->float('task_secondary_material')->default(0);

            // PPIC (Bobot 15%)
            $table->float('task_vendor')->default(0);
            $table->float('task_master_schedule')->default(0);
            $table->float('task_breakdown_schedule')->default(0);

            // Production (Bobot 30%)
            $table->float('task_fabrication')->default(0);
            $table->float('task_painting')->default(0);

            // QC (Bobot 30%)
            $table->float('task_load_test')->default(0);
            $table->float('task_full_water_test')->default(0);
            $table->float('task_mdr')->default(0);
            $table->float('task_ndt')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'task_engineering', 'task_main_material', 'task_secondary_material',
                'task_vendor', 'task_master_schedule', 'task_breakdown_schedule',
                'task_fabrication', 'task_painting', 'task_load_test',
                'task_full_water_test', 'task_mdr', 'task_ndt'
            ]);
        });
    }
};