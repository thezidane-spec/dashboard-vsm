<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * Update daftar checklist task departemen Production:
     * - Task lama yang dibuang: task_fabrication, task_painting
     * - Task baru yang ditambahkan: Marking, Cutting, Fit Up, Welding
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('task_marking')->default(0)->after('task_skema_loadtest');
            $table->boolean('task_cutting')->default(0)->after('task_marking');
            $table->boolean('task_fit_up')->default(0)->after('task_cutting');
            $table->boolean('task_welding')->default(0)->after('task_fit_up');
        });

        // Bersihkan riwayat task_completions & task_progress yang mengacu ke task lama
        \DB::table('task_completions')
            ->whereIn('task_key', ['task_fabrication', 'task_painting'])
            ->delete();

        \DB::table('task_progress')
            ->whereIn('task_key', ['task_fabrication', 'task_painting'])
            ->delete();

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['task_fabrication', 'task_painting']);
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('task_fabrication')->default(0);
            $table->boolean('task_painting')->default(0);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['task_marking', 'task_cutting', 'task_fit_up', 'task_welding']);
        });
    }
};
