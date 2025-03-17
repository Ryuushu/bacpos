<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SyncController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create']);
});
Route::get('/dashboard', [AdminController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/user/{id}', [ProfileController::class, 'show'])->name('user.show');
    Route::get('/user/{id}/edit', [ProfileController::class, 'edit'])->name('user.edit');
    Route::get('/pelanggan/search', [AdminController::class, 'search'])->name('pemilik.search');
    Route::post('/pelanggan/add', [AdminController::class, 'create'])->name('pemilik.add');

});

require __DIR__ . '/auth.php';
