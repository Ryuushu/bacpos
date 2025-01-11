<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pekerja extends Model
{
    use HasFactory;
    protected $table = 'pekerja';
    protected $primaryKey = 'id_pekerja';

    public function user()
    {
        return $this->hasOne(User::class, 'id_pekerja', 'id_pekerja');
    }
}
