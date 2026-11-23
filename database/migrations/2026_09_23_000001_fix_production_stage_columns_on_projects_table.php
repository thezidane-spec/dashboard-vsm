<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PERBAIKAN migrasi Divisi Production.
     *
     * Migrasi "2026_09_22_000001_restructure_production_tasks_on_projects_table" sempat dijalankan
     * dengan ISI VERSI PERTAMA yang keliru (menambah due_date/plan/progress PER JOB seperti
     * due_date_marking, due_date_cutting, dst — bukan per TAHAP). Karena Laravel mencatat migrasi
     * berdasarkan NAMA FILE, saat isi file itu diperbaiki, `php artisan migrate` tidak menjalankannya
     * ulang (dianggap sudah "Ran"), sehingga kolom yang benar (due_date_preparation, dst) tidak
     * pernah benar-benar dibuat.
     *
     * Migrasi INI memperbaikinya secara aman (boleh dijalankan di database manapun, apapun
     * kondisinya sekarang) dan SENGAJA TIDAK memakai ->after(...) sama sekali, supaya tidak gagal
     * gara-gara kolom acuan (task_cutting, task_welding, dsb) ternyata belum ada / urutannya beda
     * di database masing-masing:
     *   1. Membuang 18 kolom per-JOB yang salah (due_date_/plan_/progress_ untuk marking, cutting,
     *      drilling, fit_up, welding, finishing) -- HANYA jika kolom itu ada.
     *   2. Memastikan 4 kolom checklist Production dasar (task_marking, task_cutting, task_fit_up,
     *      task_welding) ada -- HANYA jika BELUM ada (jaga-jaga kalau migrasi awal proyek pernah
     *      terlewat di sebagian instalasi).
     *   3. Menambahkan 6 kolom yang BENAR (per TAHAP: due_date_preparation, plan_preparation,
     *      due_date_main_production, plan_main_production, serta 2 job checklist baru:
     *      task_drilling, task_finishing) -- HANYA jika kolom itu belum ada.
     */
    public function up(): void
    {
        // 1) Buang kolom per-JOB versi lama yang salah, kalau sempat ter-migrate
        Schema::table('projects', function (Blueprint $table) {
            $wrongColumns = [];
            foreach (['marking', 'cutting', 'drilling', 'fit_up', 'welding', 'finishing'] as $key) {
                foreach (['due_date_', 'plan_', 'progress_'] as $prefix) {
                    $column = $prefix . $key;
                    if (Schema::hasColumn('projects', $column)) {
                        $wrongColumns[] = $column;
                    }
                }
            }

            if (!empty($wrongColumns)) {
                $table->dropColumn($wrongColumns);
            }
        });

        // 2) Pastikan 4 kolom checklist Production dasar ada (tanpa ->after(), urutan kolom tidak penting)
        Schema::table('projects', function (Blueprint $table) {
            foreach (['task_marking', 'task_cutting', 'task_fit_up', 'task_welding'] as $column) {
                if (!Schema::hasColumn('projects', $column)) {
                    $table->boolean($column)->default(0);
                }
            }
        });

        // 3) Tambahkan kolom yang BENAR, kalau belum ada (tanpa ->after())
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'due_date_preparation')) {
                $table->date('due_date_preparation')->nullable();
            }
            if (!Schema::hasColumn('projects', 'plan_preparation')) {
                $table->integer('plan_preparation')->default(0);
            }
            if (!Schema::hasColumn('projects', 'due_date_main_production')) {
                $table->date('due_date_main_production')->nullable();
            }
            if (!Schema::hasColumn('projects', 'plan_main_production')) {
                $table->integer('plan_main_production')->default(0);
            }
            if (!Schema::hasColumn('projects', 'task_drilling')) {
                $table->boolean('task_drilling')->default(0);
            }
            if (!Schema::hasColumn('projects', 'task_finishing')) {
                $table->boolean('task_finishing')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $columns = ['due_date_preparation', 'plan_preparation', 'due_date_main_production', 'plan_main_production', 'task_drilling', 'task_finishing'];
            $existing = array_filter($columns, fn ($c) => Schema::hasColumn('projects', $c));

            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};