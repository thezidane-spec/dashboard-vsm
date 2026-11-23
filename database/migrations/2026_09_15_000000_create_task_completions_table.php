<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * Tabel ini mencatat KAPAN sebuah task checklist (task_drawing_ga,
     * task_bom, dsb) pertama kali ditandai selesai untuk sebuah proyek.
     * Satu baris = satu task yang sedang berstatus selesai pada satu proyek.
     * Kalau task di-uncheck lagi, barisnya dihapus (lihat DashboardController::update()).
     */
    public function up(): void
    {
        Schema::create('task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('task_key');       // contoh: task_drawing_ga, task_bom
            $table->string('department');     // engineering, procurement, ppic, production, qc_qa
            $table->timestamp('completed_at');
            $table->timestamps();

            // Satu task hanya boleh punya satu baris "selesai" aktif per proyek
            $table->unique(['project_id', 'task_key']);
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }
};
