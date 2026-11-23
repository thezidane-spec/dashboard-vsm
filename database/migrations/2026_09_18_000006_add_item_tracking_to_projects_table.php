<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * Tambah detail pelacakan per barang di Divisi Procurement:
     * - tanggal beli
     * - tanggal sampai (actual)
     * - status pengiriman: menunggu_dikirim / dalam_pengiriman / sudah_sampai
     * - status kesesuaian barang: belum_dicek / sesuai / tidak_sesuai
     *
     * Berlaku untuk 4 kategori barang: Raw Material, Accessories Part,
     * Consumable, Sparepart.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $items = ['main_material', 'secondary_material', 'consumable', 'sparepart'];

            foreach ($items as $item) {
                if (!Schema::hasColumn('projects', "task_{$item}_purchase_date")) {
                    $table->date("task_{$item}_purchase_date")->nullable();
                }
                if (!Schema::hasColumn('projects', "task_{$item}_arrival_date")) {
                    $table->date("task_{$item}_arrival_date")->nullable();
                }
                if (!Schema::hasColumn('projects', "task_{$item}_shipping_status")) {
                    $table->string("task_{$item}_shipping_status")->default('menunggu_dikirim');
                }
                if (!Schema::hasColumn('projects', "task_{$item}_match_status")) {
                    $table->string("task_{$item}_match_status")->default('belum_dicek');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $items = ['main_material', 'secondary_material', 'consumable', 'sparepart'];

            foreach ($items as $item) {
                foreach (['purchase_date', 'arrival_date', 'shipping_status', 'match_status'] as $field) {
                    if (Schema::hasColumn('projects', "task_{$item}_{$field}")) {
                        $table->dropColumn("task_{$item}_{$field}");
                    }
                }
            }
        });
    }
};
