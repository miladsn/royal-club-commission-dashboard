<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commission_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exchange_id')->constrained()->restrictOnDelete();
            $table->string('uid')->index();
            $table->decimal('trade_volume', 18, 4)->default(0);
            $table->decimal('commission', 18, 4)->default(0);
            $table->string('referral_code')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->restrictOnDelete();
            // Matching status: matched, unknown, or without an associated referral code
            $table->enum('status', ['matched', 'unmatched', 'unknown_uid'])->default('unmatched');
            $table->timestamp('reported_at')->index();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_reports');
    }
};
