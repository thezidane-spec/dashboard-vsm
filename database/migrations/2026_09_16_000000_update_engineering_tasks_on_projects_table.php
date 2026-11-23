<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * Update daftar checklist task departemen Engineering:
     * - Task lama yang dibuang: task_part_list, task_load_calc
     * - Task baru yang ditambahkan: Cutting Plan, CNC Program, Design Calculation,
     *   Design Name Plate, Design Sticker, Packing Drawing, Welding Map, Skema Loadtest
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('task_cutting_plan')->default(0)->after('task_bom');
            $table->boolean('task_cnc_program')->default(0)->after('task_cutting_plan');
            $table->boolean('task_design_calc')->default(0)->after('task_painting_calc');
            $table->boolean('task_design_name_plate')->default(0)->after('task_design_calc');
            $table->boolean('task_design_sticker')->default(0)->after('task_design_name_plate');
            $table->boolean('task_packing_drawing')->default(0)->after('task_design_sticker');
            $table->boolean('task_welding_map')->default(0)->after('task_packing_drawing');
            $table->boolean('task_skema_loadtest')->default(0)->after('task_welding_map');
        });

        // Hapus juga catatan tanggal selesai yang mengacu ke task lama (kalau ada)
        \DB::table('task_completions')
            ->whereIn('task_key', ['task_part_list', 'task_load_calc'])
            ->delete();

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['task_part_list', 'task_load_calc']);
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('task_part_list')->default(0)->after('task_bom');
            $table->boolean('task_load_calc')->default(0)->after('task_part_list');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'task_cutting_plan', 'task_cnc_program', 'task_design_calc',
                'task_design_name_plate', 'task_design_sticker', 'task_packing_drawing',
                'task_welding_map', 'task_skema_loadtest',
            ]);
        });
    }
};
