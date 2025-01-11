<?php

use App\Http\Controllers\Api\AuthControllerApi;
use App\Http\Controllers\Api\KategoriControllerApi;
use App\Http\Controllers\Api\PekerjaControllerApi;
use App\Http\Controllers\Api\ProdukControllerApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthControllerApi::class, 'register']);
Route::post('login', [AuthControllerApi::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthControllerApi::class, 'logout']);

Route::apiResource('kategori', KategoriControllerApi::class);
Route::apiResource('produk', ProdukControllerApi::class);
Route::apiResource('pekerja', PekerjaControllerApi::class);