<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Due Date & Tanggal Update PER JOBDESK Tahap Preparation (Marking, Cutting, Drilling) di
     * Divisi Production, supaya update progress lebih fleksibel per item -- pola yang sama seperti
     * migrasi "add_due_date_and_last_update_per_engineering_task" sebelumnya.
     *
     *   due_date_<task_key>   : target selesai job itu (date, boleh kosong)
     *   last_update_<task_key>: tanggal terakhir persennya diperbarui, diisi manual (date, boleh kosong)
     *
     * Idempotent (aman dijalankan berkali-kali) dan TANPA ->after(...).
     */
    private const PREPARATION_TASKS = ['task_marking', 'task_cutting', 'task_drilling'];

    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (self::PREPARATION_TASKS as $taskKey) {
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
            foreach (self::PREPARATION_TASKS as $taskKey) {
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