<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * PPIC: tambah 2 task baru (Surat Jalan, Berita Acara). 3 task lama tetap ada
     * (Master Schedule, Breakdown Schedule, PR) -- cuma label "Breakdown Schedule"
     * ditampilkan lebih singkat sebagai "Break Schedule" di tampilan (tidak ada
     * perubahan nama kolom).
     *
     * QC/QA -> "Finishing": dari 4 task lama (Load Test, Full Water Test, MDR, NDT)
     * jadi 9 task baru (Packing, Blasting, Painting, Load Test, Another Test,
     * Leak Test, MDR, NDT, BAPP). Load Test, MDR, NDT dipertahankan datanya.
     * Full Water Test dibuang, diganti Another Test + Leak Test (lebih detail).
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // PPIC: 2 task baru
            $table->boolean('task_surat_jalan')->default(0)->after('task_pr');
            $table->boolean('task_berita_acara')->default(0)->after('task_surat_jalan');

            // Finishing (dulu QC/QA): 6 task baru
            $table->boolean('task_packing')->default(0)->after('task_ndt');
            $table->boolean('task_blasting')->default(0)->after('task_packing');
            $table->boolean('task_painting')->default(0)->after('task_blasting');
            $table->boolean('task_another_test')->default(0)->after('task_load_test');
            $table->boolean('task_leak_test')->default(0)->after('task_another_test');
            $table->boolean('task_bapp')->default(0)->after('task_ndt');
        });

        // Bersihkan riwayat task_completions & task_progress untuk task yang dibuang
        \DB::table('task_completions')->where('task_key', 'task_full_water_test')->delete();
        \DB::table('task_progress')->where('task_key', 'task_full_water_test')->delete();

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['task_full_water_test']);
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('task_full_water_test')->default(0);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'task_surat_jalan', 'task_berita_acara',
                'task_packing', 'task_blasting', 'task_painting',
                'task_another_test', 'task_leak_test', 'task_bapp',
            ]);
        });
    }
};
