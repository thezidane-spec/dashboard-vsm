<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Divisi baru QA/QC (jobdesk MDR & NDT, dipisah dari Finishing).
     * Kolom jobdesk (task_mdr, task_ndt, due_date_task_*, last_update_task_*) TIDAK berubah.
     * Proyek lama: Due Date & Planning QA/QC disalin dari Finishing supaya kurva S-Curve tetap masuk akal.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'due_date_qaqc')) {
                $table->date('due_date_qaqc')->nullable();
            }
            if (!Schema::hasColumn('projects', 'plan_qaqc')) {
                $table->integer('plan_qaqc')->default(0);
            }
        });

        DB::table('projects')->update([
            'due_date_qaqc' => DB::raw('due_date_qc'),
            'plan_qaqc'     => DB::raw('plan_qc'),
        ]);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (['due_date_qaqc', 'plan_qaqc'] as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};