<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuStok extends Model
{
    use HasFactory;

    protected $table = 'kartu_stok';
    protected $primaryKey = 'id_kartu';
    public $timestamps = false;

    protected $fillable = [
        'kode_produk',
        'tanggal',
        'jenis_transaksi',
        'jumlah',
        'stok_awal',
        'stok_akhir',
        'referensi',
        'keterangan',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk');
    }
}
