<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Kolom Material Code pada Material Radar List (setelah No, sebelum Material Description). Boleh kosong.
    public function up(): void
    {
        Schema::table('material_radar_items', function (Blueprint $table) {
            if (!Schema::hasColumn('material_radar_items', 'material_code')) {
                $table->string('material_code', 100)->nullable()->after('no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('material_radar_items', function (Blueprint $table) {
            if (Schema::hasColumn('material_radar_items', 'material_code')) {
                $table->dropColumn('material_code');
            }
        });
    }
};