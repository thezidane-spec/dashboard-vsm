<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'task_vendor')) {
                $table->boolean('task_vendor')->default(0)->after('task_pr');
            }
            if (!Schema::hasColumn('projects', 'task_progress_report')) {
                $table->boolean('task_progress_report')->default(0)->after('task_surat_jalan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'task_vendor')) {
                $table->dropColumn('task_vendor');
            }
            if (Schema::hasColumn('projects', 'task_progress_report')) {
                $table->dropColumn('task_progress_report');
            }
        });
    }
};
