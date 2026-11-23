<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Weekly Report (Foto Progress): satu proyek bisa punya banyak laporan (satu per periode/minggu).
     * Foto dikelompokkan per tahap: material, fabrication, galvanized, packing, delivery.
     */
    public function up(): void
    {
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('period', 150)->nullable();          // judul periode di header (mis. "MEI - SEPTEMBER 2026")
            $table->string('customer_name', 255)->nullable();   // Name of Customer
            $table->string('corporate_group', 255)->nullable(); // Name of Corporate Group
            $table->text('address')->nullable();
            $table->string('postcode', 255)->nullable();        // Postcode / Town / Country
            $table->string('language', 100)->nullable();
            $table->string('logo', 20)->default('mutiara');     // logo header: mutiara | mudalaya
            $table->json('section_times')->nullable();          // waktu tiap tahap (input manual): {material: "...", ...}
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('weekly_report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_report_id')->constrained()->cascadeOnDelete();
            $table->string('section', 20);                      // material | fabrication | galvanized | packing | delivery
            $table->string('path');
            $table->string('original_name', 150)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_report_photos');
        Schema::dropIfExists('weekly_reports');
    }
};