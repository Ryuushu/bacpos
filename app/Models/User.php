<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Menambahkan kolom-kolom yang bisa diisi massal
    protected $fillable = [
        'email',       // Menambahkan email ke dalam fillable
        'password',    // Jangan lupa untuk menambahkan password
        'role',        // Jika diperlukan, tambahkan role
        'id_pemilik',  // Jika diperlukan, tambahkan id_pemilik
        'id_pekerja',  // Jika diperlukan, tambahkan id_pekerja
    ];

    // Kolom-kolom yang harus di-hash sebelum disimpan
    protected $hidden = [
        'password',    // Password sebaiknya tidak terdeteksi ketika ditampilkan
        'remember_token',
    ];

    // Jika Anda menggunakan laravel 8.x atau lebih tinggi, biasanya juga menambahkan `casts`
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}