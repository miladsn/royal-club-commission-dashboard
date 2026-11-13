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
        Schema::create('exchanges', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // LBank, Orbit, Bitunix
            $table->enum('type', ['exchange', 'broker'])->default('exchange');
            $table->string('slug')->unique(); // lbank, orbit, bitunix
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // Soft delete to preserve history
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchanges');
    }
};
