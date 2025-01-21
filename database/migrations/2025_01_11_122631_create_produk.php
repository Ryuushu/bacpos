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
        Schema::create('produk', function (Blueprint $table) {
            $table->id('kode_produk');
            $table->string('nama_produk', 30);
            $table->foreignId('id_toko')->references("id_toko")->on("toko")->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('kode_kategori')->references("kode_kategori")->on("kategori")->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('harga');
            $table->integer('stok')->nullable(); // Stok opsional
            $table->boolean('is_stock_managed')->default(true); // Indikator stok dikelola
            $table->string('url_img')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
