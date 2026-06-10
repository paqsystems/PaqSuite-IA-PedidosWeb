<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqPedidoswebLogin extends Model
{
    protected $table = 'pq_pedidosweb_login';

    protected $primaryKey = 'cod_usuario_web';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_usuario_web',
        'usuario',
        'password',
        'e_mail',
        'primer_login',
        'tipo_cuenta',
        'cod_asociado',
        'password_bcrypt',
        'password_sha1',
    ];

    protected $casts = [
        'primer_login' => 'boolean',
    ];
}
