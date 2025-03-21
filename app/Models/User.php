<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory,HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = true;

    protected $fillable = [
        'email',
        'password',
        'role',
        'is_verified'
    ];

    protected $hidden = [
        'password',
    ];

    public function pemilik()
    {
        return $this->hasOne(Pemilik::class, 'id_user');
    }

    public function pekerja()
    {
        return $this->hasOne(Pekerja::class, 'id_user');
    }
    public function admin()
    {
        return $this->hasOne(Admin::class, 'id_user');
    }
    public function hasRole($role)
    {
        return $this->role === $role; // Adjust based on how roles are stored
    }
   
}
