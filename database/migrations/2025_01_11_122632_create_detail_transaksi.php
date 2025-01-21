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
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaksi', 25);
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('kode_produk')->references("kode_produk")->on("produk")->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('harga');
            $table->integer('qty');
            $table->integer('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
