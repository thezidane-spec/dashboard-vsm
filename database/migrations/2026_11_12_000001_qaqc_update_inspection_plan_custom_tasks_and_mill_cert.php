<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - Jobdesk baru QA/QC "Inspection Task Plan": status Open / Close / N/A (+ Start Date, Due Date, Tanggal Update).
     * - Jobdesk custom QA/QC (nama & deskripsi manual) disimpan di kolom JSON projects.custom_tasks.
     * - Kolom Mill Certificate pada material (checklist per baris tabel Raw Material & Vendor Raw Material).
     * Jobdesk yang dihapus (Hydrolic Test QA/QC, Lift/Hydrolic Test Preparation Finish Process) tidak dihapus kolomnya
     * supaya data lama aman; hanya tidak dipakai lagi.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'task_inspection_plan')) {
                $table->unsignedTinyInteger('task_inspection_plan')->default(0);
            }
            foreach (['due_date_', 'last_update_', 'start_date_'] as $prefix) {
                if (!Schema::hasColumn('projects', $prefix . 'task_inspection_plan')) {
                    $table->date($prefix . 'task_inspection_plan')->nullable();
                }
            }
            if (!Schema::hasColumn('projects', 'status_task_inspection_plan')) {
                $table->string('status_task_inspection_plan', 10)->default('open');
            }
            if (!Schema::hasColumn('projects', 'custom_tasks')) {
                $table->json('custom_tasks')->nullable();
            }
        });

        Schema::table('material_items', function (Blueprint $table) {
            if (!Schema::hasColumn('material_items', 'mill_cert')) {
                $table->boolean('mill_cert')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (['task_inspection_plan', 'due_date_task_inspection_plan', 'last_update_task_inspection_plan', 'start_date_task_inspection_plan', 'status_task_inspection_plan', 'custom_tasks'] as $col) {
                if (Schema::hasColumn('projects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('material_items', function (Blueprint $table) {
            if (Schema::hasColumn('material_items', 'mill_cert')) {
                $table->dropColumn('mill_cert');
            }
        });
    }
};