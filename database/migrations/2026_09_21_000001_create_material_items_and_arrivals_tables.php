<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pendataan detail Raw Material (Divisi Procurement).
     *
     * material_items    : daftar material yang diminta per proyek
     *                     (kategori raw_material / vendor_material, spec, UOM, weight, qty, tanggal beli)
     * material_arrivals : catatan kedatangan tiap material (tanggal sampai + jumlah datang + remarks)
     *
     * ACTUAL   = total jumlah datang dari semua catatan kedatangan
     * DEVIASI  = ACTUAL - QTY yang diminta (0 = sesuai, + = lebih, - = kurang)
     */
    public function up(): void
    {
        Schema::create('material_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30)->default('raw_material'); // raw_material | vendor_material
            $table->string('name');
            $table->string('spec')->nullable();
            $table->string('uom', 20)->default('Pcs');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('weight', 12, 2)->nullable();             // berat per 1 UOM
            $table->date('purchase_date')->nullable();                // tanggal pembelian
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'category']);
        });

        Schema::create('material_arrivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_item_id')->constrained('material_items')->cascadeOnDelete();
            $table->date('arrival_date');                              // tanggal barang sampai
            $table->decimal('quantity', 12, 2);                        // jumlah yang sampai di tanggal tsb
            $table->string('remarks', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['material_item_id', 'arrival_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_arrivals');
        Schema::dropIfExists('material_items');
    }
};