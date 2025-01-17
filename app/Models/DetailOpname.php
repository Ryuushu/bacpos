<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailOpname extends Model
{
    use HasFactory;

    protected $table = 'detail_opname';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_opname',
        'kode_produk',
        'stok_fisik',
        'stok_sistem',
        'selisih',
        'keterangan',
    ];

    public function opname()
    {
        return $this->belongsTo(StokOpname::class, 'id_opname');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk');
    }
}
