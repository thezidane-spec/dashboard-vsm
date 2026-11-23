<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Due Date & Tanggal Update PER JOBDESK Divisi Finishing (dulu QC/QA) -- 9 job: Packing,
     * Blasting, Painting, Load Test, Another Test, Leak Test, MDR, NDT, BAPP. Pola sama seperti
     * migrasi Engineering dan Production (Preparation & Production) sebelumnya.
     *
     *   due_date_<task_key>   : target selesai job itu (date, boleh kosong)
     *   last_update_<task_key>: tanggal terakhir persennya diperbarui, diisi manual (date, boleh kosong)
     *
     * Idempotent (aman dijalankan berkali-kali) dan TANPA ->after(...).
     */
    private const FINISHING_TASKS = [
        'task_packing', 'task_blasting', 'task_painting', 'task_load_test', 'task_another_test',
        'task_leak_test', 'task_mdr', 'task_ndt', 'task_bapp',
    ];

    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (self::FINISHING_TASKS as $taskKey) {
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
            foreach (self::FINISHING_TASKS as $taskKey) {
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