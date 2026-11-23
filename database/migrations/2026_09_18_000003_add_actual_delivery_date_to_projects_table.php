<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * 'delivery_date' yang sudah ada tetap berfungsi sebagai tanggal RENCANA/target.
     * Kolom baru 'actual_delivery_date' mencatat kapan barang BENERAN dikirim,
     * supaya bisa dibandingkan: tepat waktu, lebih cepat, atau telat.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'actual_delivery_date')) {
                $table->date('actual_delivery_date')->nullable()->after('delivery_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'actual_delivery_date')) {
                $table->dropColumn('actual_delivery_date');
            }
        });
    }
};
