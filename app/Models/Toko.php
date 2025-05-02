<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $table = 'toko';
    protected $primaryKey = 'id_toko';
    public $timestamps = true;

    protected $fillable = [
        'id_pemilik',
        'nama_toko',
        'alamat_toko',
        'whatsapp',
        'instagram',
        'url_img',
        'exp_date_langganan',
        'start_date_langganan'
    ];
    public function kategori()
    {
        return $this->hasMany(Kategori::class, 'id_toko');
    }
    public function pemilik()
    {
        return $this->belongsTo(Pemilik::class, 'id_pemilik');
    }

    public function pekerja()
    {
        return $this->hasMany(Pekerja::class, 'id_toko');
    }
}
