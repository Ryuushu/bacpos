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
        Schema::create('pekerja', function (Blueprint $table) {
            $table->id('id_pekerja');
            $table->foreignId('id_user')->unique()->constrained('users','id_user')->cascadeOnDelete();
            $table->foreignId('id_toko')->references("id_toko")->on("toko")->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nama_pekerja', 100);
            $table->string('alamat_pekerja', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pekerja');
    }
};
