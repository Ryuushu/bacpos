<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi_penjualan';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = true;
    public $incrementing = false;


    protected $fillable = [
        'id_transaksi',
        'id_toko',
        'id_user',
        'totalharga',
        'pembayaran',
        'kembalian',
        'jenis_pembayaran',
        'ppn',
        'bulatppn',

    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'id_toko');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }
}
