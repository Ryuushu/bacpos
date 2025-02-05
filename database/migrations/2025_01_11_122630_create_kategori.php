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
        Schema::create('kategori', function (Blueprint $table) {
            $table->id('kode_kategori');
            $table->string('nama_kategori', 30);
            $table->boolean("is_stok");
            $table->unsignedBigInteger('id_toko');
            $table->foreign('id_toko')->references('id_toko')->on('toko')->restrictOnDelete();
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};
