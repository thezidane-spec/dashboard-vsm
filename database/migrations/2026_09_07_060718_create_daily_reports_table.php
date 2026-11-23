<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel projects
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            
            // Kategori Departemen (Engineering, Procurement, Production, dll.)
            $table->string('department'); 
            
            // Tanggal Laporan
            $table->date('report_date');
            
            // Progres Harian / Kumulatif (%)
            $table->decimal('plan_progress', 5, 2)->default(0);   // Target / Plan
            $table->decimal('actual_progress', 5, 2)->default(0); // Realisasi / Actual
            
            // Catatan & Kendala
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};