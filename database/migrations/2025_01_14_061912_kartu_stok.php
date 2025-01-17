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
        Schema::create('kartu_stok', function (Blueprint $table) {
            $table->id('id_kartu');
            $table->foreignId('kode_produk')->constrained('produk','kode_produk')->cascadeOnDelete();
            $table->dateTime('tanggal');
            $table->enum('jenis_transaksi', ['masuk', 'keluar', 'penyesuaian']);
            $table->integer('jumlah');
            $table->integer('stok_awal');
            $table->integer('stok_akhir');
            $table->string('referensi')->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_stok');
    }
};
