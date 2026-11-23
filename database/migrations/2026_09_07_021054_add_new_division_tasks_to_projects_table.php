<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('task_pr')->default(0)->after('task_breakdown_schedule');
            $table->boolean('task_drawing_ga')->default(0)->after('task_pr');
            $table->boolean('task_detail_drawing')->default(0)->after('task_drawing_ga');
            $table->boolean('task_bom')->default(0)->after('task_detail_drawing');
            $table->boolean('task_part_list')->default(0)->after('task_bom');
            $table->boolean('task_load_calc')->default(0)->after('task_part_list');
            $table->boolean('task_painting_calc')->default(0)->after('task_load_calc');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'task_pr', 'task_drawing_ga', 'task_detail_drawing', 
                'task_bom', 'task_part_list', 'task_load_calc', 'task_painting_calc'
            ]);
        });
    }
};