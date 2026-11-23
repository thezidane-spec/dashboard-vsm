<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_radars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('project_no')->nullable();
            $table->string('project_name')->nullable();
            $table->string('customer')->nullable();
            $table->string('pic_engineering')->nullable();
            $table->string('pic_ppic')->nullable();
            $table->string('pic_warehouse')->nullable();
            $table->string('pic_procurement')->nullable();
            $table->string('sheet_name')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('material_radar_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_radar_id')->constrained('material_radars')->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->string('no')->nullable();
            $table->text('description')->nullable();
            $table->string('spec')->nullable();
            $table->double('req_qty')->nullable();
            $table->double('avail_qty')->nullable();
            $table->string('bom_no')->nullable();
            $table->date('bom_date')->nullable();
            $table->string('pr_no')->nullable();
            $table->double('pr_qty')->nullable();
            $table->date('pr_date')->nullable();
            $table->string('po_no')->nullable();
            $table->double('po_qty')->nullable();
            $table->date('po_date')->nullable();
            $table->date('required_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->double('arrival_qty')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_radar_items');
        Schema::dropIfExists('material_radars');
    }
};