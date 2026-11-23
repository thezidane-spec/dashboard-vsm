<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jobdesk baru di divisi QA/QC: Welding Report, Painting Report, Galvanized Report.
     * Masing-masing punya progress (%), Start Date, Due Date, dan Tanggal Update seperti jobdesk lain.
     */
    private array $tasks = ['task_welding_report', 'task_painting_report', 'task_galvanized_report'];

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
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach ($this->tasks as $task) {
                foreach ([$task, 'due_date_' . $task, 'last_update_' . $task, 'start_date_' . $task] as $col) {
                    if (Schema::hasColumn('projects', $col)) {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};