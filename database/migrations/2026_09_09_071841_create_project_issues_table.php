<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('department'); // engineering, procurement, ppic, production, qc_qa
            $table->string('category'); // Material, Equipment, Drawing/Technical, Manpower, Customer/External
            $table->text('title'); // Judul Singkat Kendala
            $table->text('description')->nullable(); // Rincian Masalah & Impact
            $table->text('action_plan')->nullable(); // Rencana Mitigasi / Solusi
            $table->enum('priority', ['LOW', 'MEDIUM', 'CRITICAL'])->default('MEDIUM');
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'RESOLVED'])->default('OPEN');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_issues');
    }
};
