<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action'); // e.g. "UPDATE_PROGRESS", "CHECK_MILESTONE", "CREATE_PROJECT"
            $table->string('department')->nullable();
            $table->text('description');
            $table->json('old_values')->nullable(); // Data sebelum diubah
            $table->json('new_values')->nullable(); // Data setelah diubah
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
