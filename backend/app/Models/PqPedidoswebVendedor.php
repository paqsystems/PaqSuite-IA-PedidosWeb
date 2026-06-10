<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqPedidoswebVendedor extends Model
{
    protected $table = 'pq_pedidosweb_vendedores';

    protected $primaryKey = 'cod_vended';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_vended',
        'nombre',
        'supervisor',
        'mail_supervisor',
        'cod_login',
        'e_mail',
    ];

    protected $casts = [
        'supervisor' => 'boolean',
    ];
}
