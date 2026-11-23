<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->date('contract_review')->nullable()->after('due_date');
            $table->date('start_production')->nullable()->after('contract_review');
            $table->date('delivery_date')->nullable()->after('start_production');
            $table->integer('quantity')->default(1)->after('delivery_date');
            $table->string('fabricator')->nullable()->after('quantity');
            $table->text('note')->nullable()->after('fabricator');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'contract_review',
                'start_production',
                'delivery_date',
                'quantity',
                'fabricator',
                'note',
            ]);
        });
    }
};