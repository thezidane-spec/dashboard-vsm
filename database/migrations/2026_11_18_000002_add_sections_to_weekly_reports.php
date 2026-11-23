<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tahap foto progress dibuat fleksibel per laporan: judul bisa diubah dan jumlahnya bisa ditambah / dikurangi.
     * Format: [{"key":"material","title":"Material","time":"MEI - SEPTEMBER 2026"}, ...]
     * Laporan lama (kolom ini kosong) tetap dibaca sebagai 5 tahap bawaan.
     */
    public function up(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->json('sections')->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_reports', function (Blueprint $table) {
            $table->dropColumn('sections');
        });
    }
};