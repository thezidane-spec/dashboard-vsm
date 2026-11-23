<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** false = material yang sudah ada lalu dicocokkan ke radar: hanya tanggal diperlukan & kedatangan yang disinkronkan */
    public function up(): void
    {
        Schema::table('material_items', function (Blueprint $table) {
            $table->boolean('radar_full_sync')->default(true)->after('radar_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('material_items', fn(Blueprint $t) => $t->dropColumn('radar_full_sync'));
    }
};