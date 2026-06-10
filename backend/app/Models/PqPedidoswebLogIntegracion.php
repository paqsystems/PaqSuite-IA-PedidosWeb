<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqPedidoswebLogIntegracion extends Model
{
    protected $table = 'pq_pedidosweb_logs_integracion';

    protected $primaryKey = 'id_log';

    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'tipo',
        'severidad',
        'origen',
        'mensaje',
        'payload',
        'procesado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'procesado' => 'boolean',
    ];
}
