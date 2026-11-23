<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * Update checklist Divisi Procurement dari 2 task jadi 4 task:
     * Raw Material (dulu Main Material), Accessories Part (dulu Secondary
     * Material), + 2 task baru: Consumable dan Sparepart.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'task_consumable')) {
                $table->boolean('task_consumable')->default(0)->after('task_secondary_material');
            }
            if (!Schema::hasColumn('projects', 'task_sparepart')) {
                $table->boolean('task_sparepart')->default(0)->after('task_consumable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'task_consumable')) {
                $table->dropColumn('task_consumable');
            }
            if (Schema::hasColumn('projects', 'task_sparepart')) {
                $table->dropColumn('task_sparepart');
            }
        });
    }
};
