<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqPedidoswebProvincia extends Model
{
    protected $table = 'pq_pedidosweb_provincias';

    protected $primaryKey = 'cod_provin';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_provin',
        'nombre_pro',
    ];
}
