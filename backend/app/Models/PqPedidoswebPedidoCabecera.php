<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqPedidoswebPedidoCabecera extends Model
{
    protected $table = 'pq_pedidosweb_pedidoscabecera';

    protected $primaryKey = 'cod_pedido';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_pedido',
        'cod_cliente',
        'fecha',
        'estado',
        'cod_usuario_web',
        'fecha_modif',
        'total',
        'total_iva',
        'cod_vended',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_modif' => 'datetime',
        'estado' => 'integer',
        'total' => 'decimal:2',
        'total_iva' => 'decimal:2',
    ];
}
