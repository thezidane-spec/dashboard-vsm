<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Due Date & Tanggal Update PER JOBDESK Divisi Procurement -- KECUALI Raw Material
     * (task_main_material), karena item itu sudah punya field "Tanggal Beli" & "Tanggal Sampai"
     * sendiri (dan popup Detail Material) yang berfungsi serupa.
     *
     * Hanya 3 job: Accessories Part, Consumable, Sparepart. Pola sama seperti divisi lain.
     *
     *   due_date_<task_key>   : target selesai job itu (date, boleh kosong)
     *   last_update_<task_key>: tanggal terakhir persennya diperbarui, diisi manual (date, boleh kosong)
     *
     * Idempotent (aman dijalankan berkali-kali) dan TANPA ->after(...).
     */
    private const PROCUREMENT_TASKS = ['task_secondary_material', 'task_consumable', 'task_sparepart'];

    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (self::PROCUREMENT_TASKS as $taskKey) {
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
            foreach (self::PROCUREMENT_TASKS as $taskKey) {
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