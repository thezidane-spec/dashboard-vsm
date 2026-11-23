<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jobdesk baru:
     *  - Preparation : Drilling (task_prep_drilling), Rolling, Bending, Machining
     *  - Finish Process : Galvanizing, Electro Plating, Hydrolic Test Preparation
     *  - QA/QC : Material Inspection, Load Test, Another Test, Leak Test, Hydrolic Test
     * Tiap jobdesk punya: progress (%), Start Date, Due Date, Tanggal Update.
     *
     * Kolom tasks_disabled (JSON) = pilihan dropdown "Jobdesk" per divisi/tahap selain Engineering:
     *  {"ppic":[...],"preparation":[...],"main_production":[...],"finishing":[...],"qaqc":[...]}
     * Yang disimpan adalah jobdesk yang DIMATIKAN; NULL = semua aktif (proyek lama tidak berubah).
     */
    private array $tasks = [
        'task_prep_drilling', 'task_rolling', 'task_bending', 'task_machining',
        'task_galvanizing', 'task_electro_plating', 'task_hydrolic_test',
        'task_material_inspection', 'task_qa_load_test', 'task_qa_another_test',
        'task_qa_leak_test', 'task_qa_hydrolic_test',
    ];

    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach ($this->tasks as $task) {
                if (!Schema::hasColumn('projects', $task)) {
                    $table->unsignedTinyInteger($task)->default(0);
                }
                foreach (['due_date_', 'last_update_', 'start_date_'] as $prefix) {
                    if (!Schema::hasColumn('projects', $prefix . $task)) {
                        $table->date($prefix . $task)->nullable();
                    }
                }
            }
            if (!Schema::hasColumn('projects', 'tasks_disabled')) {
                $table->json('tasks_disabled')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $drop = ['tasks_disabled'];
            foreach ($this->tasks as $task) {
                $drop[] = $task;
                foreach (['due_date_', 'last_update_', 'start_date_'] as $prefix) {
                    $drop[] = $prefix . $task;
                }
            }
            foreach ($drop as $col) {
                if (Schema::hasColumn('projects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};