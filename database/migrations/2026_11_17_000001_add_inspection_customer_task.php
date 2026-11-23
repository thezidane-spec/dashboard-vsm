<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Jobdesk baru QA/QC: Inspections by Customer (persen + Start Date, Due Date, Tanggal Update)
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'task_inspection_customer')) {
                $table->unsignedTinyInteger('task_inspection_customer')->default(0);
            }
            foreach (['due_date_', 'last_update_', 'start_date_'] as $prefix) {
                if (!Schema::hasColumn('projects', $prefix . 'task_inspection_customer')) {
                    $table->date($prefix . 'task_inspection_customer')->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (['task_inspection_customer', 'due_date_task_inspection_customer', 'last_update_task_inspection_customer', 'start_date_task_inspection_customer'] as $col) {
                if (Schema::hasColumn('projects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};