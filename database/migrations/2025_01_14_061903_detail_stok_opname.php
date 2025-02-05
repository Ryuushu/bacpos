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
        Schema::create('detail_opname', function (Blueprint $table) {
            $table->id('id_detail');
            $table->foreignId('id_opname')->constrained('stok_opname','id_opname')->restrictOnDelete();
            $table->foreignId('kode_produk')->constrained('produk','kode_produk')->restrictOnDelete();
            $table->integer('stok_fisik');
            $table->integer('stok_sistem');
            $table->integer('selisih');
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_stok_opname');
    }
};
