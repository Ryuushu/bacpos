<?php

use App\Http\Controllers\Api\AuthControllerApi;
use App\Http\Controllers\Api\KategoriControllerApi;
use App\Http\Controllers\Api\PekerjaControllerApi;
use App\Http\Controllers\Api\ProdukControllerApi;
use App\Http\Controllers\Api\TokoControllerApi;
use App\Http\Controllers\Api\DashboardControllerApi;
use App\Http\Controllers\Api\KartuStokControllerApi;
use App\Http\Controllers\Api\StokControllerApi;
use App\Http\Controllers\Api\TransaksiControllerApi;
use App\Http\Controllers\Api\TransaksiPembelianControllerApi;
use App\Http\Controllers\Api\LaporanControllerApi;
use Illuminate\Support\Facades\Route;


Route::post('register', [AuthControllerApi::class, 'register']);
Route::post('login', [AuthControllerApi::class, 'login']);
Route::post('verify-otp', [AuthControllerApi::class, 'verifyOtp']);
Route::post('forgot-password', [AuthControllerApi::class, 'forgotPassword']);
Route::post('reset-password', [AuthControllerApi::class, 'resetPassword']);
Route::get('laporanpenjualan/export/{type}/{idtoko}', [LaporanControllerApi::class, 'exportLaporanPenjualan']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthControllerApi::class, 'logout']);
    Route::get('listtokopemilik', [DashboardControllerApi::class, 'listtokobypemilik']);
    Route::get('dashboard', [DashboardControllerApi::class, 'index']);
    Route::get('kartustok/{id}', [StokControllerApi::class, 'show']);
    Route::get('produk/{id}/{bool}', [ProdukControllerApi::class, 'shows']);
    Route::get('riwayattransaksi/{id_toko}', [TransaksiControllerApi::class, 'riwayat']);
    Route::get('kartustok/{kodep}/{type}', [KartuStokControllerApi::class, 'shows']);
    Route::post('/s', [StokControllerApi::class, 'searchBykodepwitharray']);
    Route::get('/dashboardtoko/{idtoko}', [TokoControllerApi::class, 'dashboardtoko']);
    Route::post('/svopname', [StokControllerApi::class, 'addstokopname']);
    Route::post('/produk/{id}',[ProdukControllerApi::class, 'update']);
    Route::post('/toko/{id}',[TokoControllerApi::class, 'update']);
    Route::get('riwayattransaksipembelian/{id_toko}', [TransaksiPembelianControllerApi::class, 'riwayat']);
    // Route::get('laporanpenjualan/export/{type}/{idtoko}', [LaporanControllerApi::class, 'exportLaporanPenjualan']);
    Route::get('laporanpembelian/export/{type}/{idtoko}', [LaporanControllerApi::class, 'exportLaporanPembelian']);

    Route::apiResource('toko', TokoControllerApi::class);
    Route::apiResource('pekerja', PekerjaControllerApi::class);
    Route::apiResource('kategori', KategoriControllerApi::class);
    Route::apiResource('produk', ProdukControllerApi::class);
    Route::apiResource('transaksi', TransaksiControllerApi::class);
    Route::apiResource('transaksipembelian', TransaksiPembelianControllerApi::class);
    // Route::apiResource('transaksi', TransaksiControllerApi::class);
    // Route::apiResource('stok-opname', StokOpnameControllerApi::class);
    // Route::apiResource('kartu-stok', KartuStokControllerApi::class);
});
