<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_reports', function (Blueprint $table) {
            $table->index(
                ['exchange_id', 'uid', 'reported_at', 'trade_volume'],
                'reports_lookup_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('commission_reports', function (Blueprint $table) {
            $table->dropIndex('reports_lookup_index');
        });
    }
};
