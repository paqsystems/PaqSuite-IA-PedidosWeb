<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqPedidoswebPerfil extends Model
{
    protected $table = 'pq_pedidosweb_perfil';

    protected $primaryKey = 'cod_perfil';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_perfil',
        'descripcion',
    ];
}
