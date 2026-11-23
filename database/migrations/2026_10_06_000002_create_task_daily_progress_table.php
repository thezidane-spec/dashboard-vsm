<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat progress HARIAN per jobdesk -- bahan data S-Curve.
     *
     * Satu baris = nilai progress (0-100) sebuah jobdesk pada satu tanggal.
     * Kalau progress diubah lebih dari sekali dalam sehari, barisnya diperbarui
     * (nilai terakhir hari itu yang dipakai), bukan ditumpuk.
     */
    public function up(): void
    {
        if (Schema::hasTable('task_daily_progress')) {
            return;
        }

        Schema::create('task_daily_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('task_key');
            $table->unsignedTinyInteger('progress');
            $table->date('log_date');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu jobdesk hanya punya satu baris per proyek per tanggal
            $table->unique(['project_id', 'task_key', 'log_date']);
            $table->index('log_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_daily_progress');
    }
};