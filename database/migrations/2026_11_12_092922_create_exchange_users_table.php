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
        Schema::create('exchange_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exchange_id')->constrained()->restrictOnDelete();
            $table->string('uid')->index();
            $table->foreignId('referral_code_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();

            // UID is unique within each exchange
            $table->unique(['exchange_id', 'uid']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_users');
    }
};
