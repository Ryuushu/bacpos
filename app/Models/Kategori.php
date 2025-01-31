<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori';
    protected $primaryKey = 'kode_kategori';
    public $timestamps = true;

    protected $fillable = [
        'nama_kategori',
        'id_toko'
    ];
    public function toko()
    {
        return $this->belongsTo(Toko::class, 'id_toko');
    }
    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_kategori');
    }
}
