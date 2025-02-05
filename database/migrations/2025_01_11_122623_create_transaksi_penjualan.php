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
        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->string('id_transaksi',25)->primary();
            $table->foreignId('id_toko')->constrained('toko','id_toko')->restrictOnDelete();
            $table->foreignId('id_user')->constrained('users','id_user')->restrictOnDelete();
            $table->integer('totalharga');
            $table->integer('pembayaran');
            $table->integer('kembalian');
            $table->enum('jenis_pembayaran', ['Tunai', 'Non-Tunai']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_penjualan');
    }
};
