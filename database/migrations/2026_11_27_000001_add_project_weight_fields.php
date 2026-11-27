<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('total_material_weight_kg', 15, 3)->nullable();
            $table->decimal('finished_project_weight_kg', 15, 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['total_material_weight_kg', 'finished_project_weight_kg']);
        });
    }
};
