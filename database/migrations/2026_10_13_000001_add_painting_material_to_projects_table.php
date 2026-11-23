<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Procurement kini hanya 3 jobdesk: Raw Material, Accessories, Painting.
     * Jobdesk "Painting" (procurement) memakai kolom baru task_painting_material
     * (kolom task_painting milik Finishing TIDAK disentuh).
     *
     * Kolom task_consumable & task_sparepart TIDAK dihapus (data lama aman), hanya tidak dihitung lagi.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'task_painting_material')) {
                $table->float('task_painting_material')->default(0);
            }
            foreach (['due_date_task_painting_material', 'last_update_task_painting_material',
                      'task_painting_material_purchase_date', 'task_painting_material_arrival_date'] as $column) {
                if (!Schema::hasColumn('projects', $column)) {
                    $table->date($column)->nullable();
                }
            }
            if (!Schema::hasColumn('projects', 'task_painting_material_shipping_status')) {
                $table->string('task_painting_material_shipping_status')->default('menunggu_dikirim');
            }
            if (!Schema::hasColumn('projects', 'task_painting_material_match_status')) {
                $table->string('task_painting_material_match_status')->default('belum_dicek');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (['task_painting_material', 'due_date_task_painting_material', 'last_update_task_painting_material',
                      'task_painting_material_purchase_date', 'task_painting_material_arrival_date',
                      'task_painting_material_shipping_status', 'task_painting_material_match_status'] as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};