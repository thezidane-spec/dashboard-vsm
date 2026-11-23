<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration susulan: nambah kolom task_vendor yang kelewat
     * di migration sebelumnya (2026_09_18_000001).
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'task_vendor')) {
                $table->boolean('task_vendor')->default(0)->after('task_pr');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'task_vendor')) {
                $table->dropColumn('task_vendor');
            }
        });
    }
};
