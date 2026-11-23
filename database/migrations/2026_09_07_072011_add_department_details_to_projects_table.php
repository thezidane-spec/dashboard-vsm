<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Engineering
            $table->date('due_date_engineering')->nullable();
            $table->integer('plan_engineering')->default(0);

            // Procurement
            $table->date('due_date_procurement')->nullable();
            $table->integer('plan_procurement')->default(0);

            // PPIC
            $table->date('due_date_ppic')->nullable();
            $table->integer('plan_ppic')->default(0);

            // Production
            $table->date('due_date_production')->nullable();
            $table->integer('plan_production')->default(0);

            // QC / QA
            $table->date('due_date_qc')->nullable();
            $table->integer('plan_qc')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'due_date_engineering', 'plan_engineering',
                'due_date_procurement', 'plan_procurement',
                'due_date_ppic', 'plan_ppic',
                'due_date_production', 'plan_production',
                'due_date_qc', 'plan_qc',
            ]);
        });
    }
};