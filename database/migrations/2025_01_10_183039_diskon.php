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
        Schema::create('diskon', function (Blueprint $table) {
            $table->id('id_diskon');
            $table->foreignId('id_toko')->constrained('toko','id_toko')->restrictOnDelete();
            $table->string('nama_diskon', 50)->unique();
            $table->string('value', 100);
            $table->enum('tipe', ['nominal', 'persen']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskon');
    }
};
