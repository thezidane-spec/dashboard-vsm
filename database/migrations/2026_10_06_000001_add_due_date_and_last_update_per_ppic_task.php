<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Due Date & Tanggal Update PER JOBDESK Divisi PPIC -- 7 job: Master Schedule, Break Schedule,
     * Purchase Requisition, Vendor, Surat Jalan, Progress Report, Berita Acara. Pola sama seperti
     * migrasi Engineering, Production, Finishing, dan Procurement sebelumnya.
     *
     *   due_date_<task_key>   : target selesai job itu (date, boleh kosong)
     *   last_update_<task_key>: tanggal terakhir persennya diperbarui, diisi manual (date, boleh kosong)
     *
     * Idempotent (aman dijalankan berkali-kali) dan TANPA ->after(...).
     */
    private const PPIC_TASKS = [
        'task_master_schedule', 'task_breakdown_schedule', 'task_pr', 'task_vendor',
        'task_surat_jalan', 'task_progress_report', 'task_berita_acara',
    ];

    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (self::PPIC_TASKS as $taskKey) {
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
            foreach (self::PPIC_TASKS as $taskKey) {
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