<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     *
     * 'due_date_procurement' yang sudah ada berfungsi sebagai target/ETA
     * kapan material harus datang. Kolom baru 'actual_material_arrival_date'
     * mencatat kapan material BENERAN datang, supaya bisa dibandingkan.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'actual_material_arrival_date')) {
                $table->date('actual_material_arrival_date')->nullable()->after('task_secondary_material');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'actual_material_arrival_date')) {
                $table->dropColumn('actual_material_arrival_date');
            }
        });
    }
};
