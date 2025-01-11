<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemilik extends Model
{
    use HasFactory;

    // Nama tabel jika berbeda dengan nama model
    protected $table = 'pemilik';

    // Kolom yang dapat diisi (Mass Assignment)
    protected $fillable = [
        'nama_pemilik',
        'alamat_pemilik',
    ];

    // Jika Anda ingin menggunakan timestamps, pastikan kolom created_at dan updated_at ada
    public $timestamps = true;

    // Relasi dengan model User
    public function users()
    {
        return $this->hasMany(User::class, 'id_pemilik');
    }
}
