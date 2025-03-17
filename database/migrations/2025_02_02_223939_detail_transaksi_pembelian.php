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
        Schema::create('detail_transaksi_pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaksi_pembelian', 25);
            $table->foreign('id_transaksi_pembelian')->references('id_transaksi_pembelian')->on('transaksi_pembelian')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('kode_produk')->references("kode_produk")->on("produk")->restrictOnDelete()->cascadeOnUpdate();
            $table->integer('harga');
            $table->integer('qty');
            $table->integer('subtotal');
            $table->integer('harga_beli');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi_pembelian');
    }
};
