<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * REMARKS REPORT per proyek (menggantikan tampilan Issue Tracking).
     *
     *   remarks_problems : [{"text": "...", "date": "2026-10-20"}]            -> Remarks Problem + tanggal masalah
     *   remarks_tasks    : [{"text": "...", "due_date": "2026-10-23"}]        -> Task revisi + due date
     *   remarks_pics     : [{"name": "Cahya", "department": "ppic"}]          -> PIC + departemen
     *   remarks_updated_at : kapan terakhir di-UPDATE
     *
     * Tabel project_issues yang lama TIDAK diubah/dihapus.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (['remarks_problems', 'remarks_tasks', 'remarks_pics'] as $column) {
                if (!Schema::hasColumn('projects', $column)) {
                    $table->json($column)->nullable();
                }
            }
            if (!Schema::hasColumn('projects', 'remarks_updated_at')) {
                $table->timestamp('remarks_updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (['remarks_problems', 'remarks_tasks', 'remarks_pics', 'remarks_updated_at'] as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};