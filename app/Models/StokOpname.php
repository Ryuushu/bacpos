<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    use HasFactory;

    protected $table = 'stok_opname';
    protected $primaryKey = 'id_opname';
    public $timestamps = true;

    protected $fillable = [
        'id_toko',
        'tanggal_opname',
        'keterangan',
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'id_toko');
    }

    public function detailOpname()
    {
        return $this->hasMany(DetailOpname::class, 'id_opname');
    }
}
