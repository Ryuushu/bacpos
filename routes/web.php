<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [AdminController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/user/{id}', [ProfileController::class, 'show'])->name('user.show');
    Route::get('/user/{id}/edit', [ProfileController::class, 'edit'])->name('user.edit');
    Route::get('/pelanggan/search', [AdminController::class, 'search'])->name('pemilik.search');
});

require __DIR__ . '/auth.php';
