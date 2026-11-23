<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * Tabel ini melengkapi task_completions (yang mencatat kapan SELESAI).
     * task_progress mencatat task mana yang statusnya SEDANG DIKERJAKAN
     * (belum selesai, tapi bukan juga "belum disentuh sama sekali").
     *
     * Aturan sederhana:
     * - Kalau ada baris di sini untuk (project_id, task_key)   -> status = "Sedang Dikerjakan"
     * - Kalau task_key sudah tercatat SELESAI (kolom boolean)  -> status = "Selesai"
     * - Kalau tidak ada baris di sini & belum selesai          -> status = "Belum Dikerjakan"
     */
    public function up(): void
    {
        Schema::create('task_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('task_key');
            $table->string('department');
            $table->timestamp('started_at');
            $table->timestamps();

            // Satu task hanya boleh punya satu baris "sedang dikerjakan" aktif per proyek
            $table->unique(['project_id', 'task_key']);
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_progress');
    }
};
