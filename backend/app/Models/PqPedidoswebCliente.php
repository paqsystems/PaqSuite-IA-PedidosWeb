<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqPedidoswebCliente extends Model
{
    protected $table = 'pq_pedidosweb_clientes';

    protected $primaryKey = 'cod_client';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_client',
        'nombre',
        'fantasia',
        'cod_vended',
        'lista_precios',
        'cod_condvta',
        'cod_transpor',
        'bonificacion',
        'nivel',
        'expreso',
        'expreso_dire',
        'cod_login',
        'e_mail',
        'leyenda_1',
        'leyenda_2',
        'leyenda_3',
        'leyenda_4',
        'leyenda_5',
    ];

    protected $casts = [
        'lista_precios' => 'integer',
        'cod_condvta' => 'integer',
        'bonificacion' => 'decimal:4',
        'nivel' => 'integer',
    ];
}
