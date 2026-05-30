<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'codigo',
        'name_user',
        'email',
        'password_hash',
        'activo',
        'inhabilitado',
        'first_login',
        'locale',
        'theme',
    ];

    protected $hidden = [
        'password_hash',
        'password',
        'token',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'inhabilitado' => 'boolean',
        'first_login' => 'boolean',
        'supervisor' => 'boolean',
    ];

    public function getAuthPassword(): string
    {
        if (! empty($this->password_hash)) {
            return (string) $this->password_hash;
        }

        return (string) ($this->password ?? '');
    }

    public function permiso(): HasOne
    {
        return $this->hasOne(PqPermiso::class, 'id_usuario', 'id');
    }
}
