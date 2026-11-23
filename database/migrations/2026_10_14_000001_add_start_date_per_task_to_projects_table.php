<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Start Date (tanggal mulai) untuk SEMUA jobdesk: kolom start_date_<task_key> (date, boleh kosong).
     * Idempotent (aman dijalankan berkali-kali) dan tanpa ->after(...).
     */
    private const TASKS = [
        'task_drawing_ga',
        'task_detail_drawing',
        'task_cutting_plan',
        'task_cnc_program',
        'task_bom',
        'task_painting_calc',
        'task_design_calc',
        'task_design_name_plate',
        'task_design_sticker',
        'task_packing_drawing',
        'task_welding_map',
        'task_skema_loadtest',
        'task_main_material',
        'task_secondary_material',
        'task_painting_material',
        'task_master_schedule',
        'task_breakdown_schedule',
        'task_pr',
        'task_vendor',
        'task_surat_jalan',
        'task_progress_report',
        'task_berita_acara',
        'task_bapp',
        'task_marking',
        'task_cutting',
        'task_drilling',
        'task_fit_up',
        'task_welding',
        'task_finishing',
        'task_packing',
        'task_blasting',
        'task_painting',
        'task_load_test',
        'task_another_test',
        'task_leak_test',
        'task_mdr',
        'task_ndt',
    ];

    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (self::TASKS as $taskKey) {
                $column = 'start_date_' . $taskKey;
                if (!Schema::hasColumn('projects', $column)) {
                    $table->date($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (self::TASKS as $taskKey) {
                $column = 'start_date_' . $taskKey;
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};