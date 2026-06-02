<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqPedidoswebCondicionVenta extends Model
{
    protected $table = 'pq_pedidosweb_condventa';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
    ];

    protected $casts = [
        'codigo' => 'integer',
    ];
}
