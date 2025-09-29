<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';
    protected $primaryKey = 'id_pengguna';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id_pengguna', 'nama', 'email', 'telepon', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['terakhir_login' => 'datetime'];

    // Laravel expects 'password' col; mapping dari 'kata_sandi' → 'password'
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
