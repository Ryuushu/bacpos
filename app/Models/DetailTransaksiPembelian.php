<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksiPembelian extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi_pembelian';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_transaksi_pembelian',
        'kode_produk',
        'harga',
        'qty',
        'subtotal',
        'harga_beli',
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiPembelian::class, 'id_transaksi_pembelian');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk');
    }
}
