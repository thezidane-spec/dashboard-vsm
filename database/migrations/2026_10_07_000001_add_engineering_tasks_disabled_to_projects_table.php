<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jobdesk Engineering yang TIDAK dipakai di sebuah proyek (dipilih lewat dropdown "Jobdesk").
     *
     * Berisi daftar task_key yang dinonaktifkan, mis. ["task_design_sticker","task_painting_calc"].
     * NULL / kosong = semua jobdesk aktif, jadi proyek lama tidak berubah sama sekali.
     * Yang disimpan adalah daftar yang DIMATIKAN (bukan yang aktif) supaya kalau nanti ada jobdesk
     * baru, otomatis aktif di semua proyek.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'engineering_tasks_disabled')) {
                $table->json('engineering_tasks_disabled')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'engineering_tasks_disabled')) {
                $table->dropColumn('engineering_tasks_disabled');
            }
        });
    }
};