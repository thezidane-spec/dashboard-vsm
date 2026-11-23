<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tanggal aktual selesai proyek (diisi saat semua jobdesk 100%).
     * Dipakai status CLOSE / CLOSE TERLAMBAT.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'actual_finish_date')) {
                $table->date('actual_finish_date')->nullable()->after('actual_delivery_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'actual_finish_date')) {
                $table->dropColumn('actual_finish_date');
            }
        });
    }
};