<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jobdesk baru di Finish Process: "Finishing After Galvanized" (task_finishing_after_galv)
     * Punya progress (%), Start Date, Due Date, dan Tanggal Update seperti jobdesk lain.
     */
    private string $task = 'task_finishing_after_galv';

    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', $this->task)) {
                $table->unsignedTinyInteger($this->task)->default(0);
            }
            foreach (['due_date_', 'last_update_', 'start_date_'] as $prefix) {
                if (!Schema::hasColumn('projects', $prefix . $this->task)) {
                    $table->date($prefix . $this->task)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach ([$this->task, 'due_date_' . $this->task, 'last_update_' . $this->task, 'start_date_' . $this->task] as $col) {
                if (Schema::hasColumn('projects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};