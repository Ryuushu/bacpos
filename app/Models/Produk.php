<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'kode_produk';
    public $timestamps = true;

    protected $fillable = [
        'id_toko',
        'kode_kategori',
        'nama_produk',
        'harga',
        'stok',
        'stok',
        'url_img',
        'is_stock_managed',
        'harga_beli'
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'id_toko');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kode_kategori');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'kode_produk');
    }

    public function detailOpname()
    {
        return $this->hasMany(DetailOpname::class, 'kode_produk');
    }
}
