<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqPedidoswebTransporte extends Model
{
    protected $table = 'pq_pedidosweb_transportes';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
    ];
}
