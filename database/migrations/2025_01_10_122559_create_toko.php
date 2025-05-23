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
        Schema::create('toko', function (Blueprint $table) {
            $table->id('id_toko');
            $table->foreignId('id_pemilik')->references("id_pemilik")->on("pemilik")->restrictOnDelete()->cascadeOnUpdate();
            $table->string('nama_toko', 100);
            $table->string('alamat_toko', 100);
            $table->string('whatsapp', 15)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('url_img')->nullable();
            $table->boolean('is_verified')->default(0);
            $table->timestamp('start_date_langganan')->nullable();
            $table->timestamp('exp_date_langganan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toko');
    }
};
