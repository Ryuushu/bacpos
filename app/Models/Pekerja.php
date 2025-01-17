<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pekerja extends Model
{
    use HasFactory;

    protected $table = 'pekerja';
    protected $primaryKey = 'id_pekerja';
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'id_toko',
        'nama_pekerja',
        'alamat_pekerja'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'id_toko');
    }
}

