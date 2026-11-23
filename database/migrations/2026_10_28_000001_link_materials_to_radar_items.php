<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Raw Material <-> Material Radar List memakai satu data yang sama:
     * - material_items.radar_item_id    : baris radar asal material ini
     * - material_arrivals.source        : 'radar' = kedatangan yang berasal dari kolom Arrival di radar
     */
    public function up(): void
    {
        Schema::table('material_items', function (Blueprint $table) {
            $table->unsignedBigInteger('radar_item_id')->nullable()->after('project_id')->index();
        });
        Schema::table('material_arrivals', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('material_items', fn(Blueprint $t) => $t->dropColumn('radar_item_id'));
        Schema::table('material_arrivals', fn(Blueprint $t) => $t->dropColumn('source'));
    }
};