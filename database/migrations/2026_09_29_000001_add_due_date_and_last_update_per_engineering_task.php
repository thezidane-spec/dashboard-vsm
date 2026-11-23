<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Due Date & Tanggal Update PER JOBDESK Divisi Engineering (12 job), supaya update progress
     * lebih fleksibel per item, bukan cuma satu Due Date untuk seluruh divisi.
     *
     *   due_date_<task_key>   : target selesai job itu (date, boleh kosong)
     *   last_update_<task_key>: tanggal terakhir persennya diperbarui, diisi manual (date, boleh kosong)
     *
     * Dibuat idempotent (aman dijalankan berkali-kali / di database yang kondisinya berbeda-beda)
     * dan TANPA ->after(...), mengikuti pelajaran dari migrasi Production sebelumnya.
     */
    private const ENGINEERING_TASKS = [
        'task_drawing_ga', 'task_detail_drawing', 'task_cutting_plan', 'task_cnc_program', 'task_bom',
        'task_painting_calc', 'task_design_calc', 'task_design_name_plate', 'task_design_sticker',
        'task_packing_drawing', 'task_welding_map', 'task_skema_loadtest',
    ];

    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (self::ENGINEERING_TASKS as $taskKey) {
                $dueColumn    = 'due_date_' . $taskKey;
                $updateColumn = 'last_update_' . $taskKey;

                if (!Schema::hasColumn('projects', $dueColumn)) {
                    $table->date($dueColumn)->nullable();
                }
                if (!Schema::hasColumn('projects', $updateColumn)) {
                    $table->date($updateColumn)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $columns = [];
            foreach (self::ENGINEERING_TASKS as $taskKey) {
                foreach (['due_date_' . $taskKey, 'last_update_' . $taskKey] as $column) {
                    if (Schema::hasColumn('projects', $column)) {
                        $columns[] = $column;
                    }
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};