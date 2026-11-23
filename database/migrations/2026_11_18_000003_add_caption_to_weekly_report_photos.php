<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Keterangan per foto (tampil tepat di bawah foto pada laporan Excel). */
    public function up(): void
    {
        Schema::table('weekly_report_photos', function (Blueprint $table) {
            $table->string('caption', 255)->nullable()->after('original_name');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_report_photos', function (Blueprint $table) {
            $table->dropColumn('caption');
        });
    }
};