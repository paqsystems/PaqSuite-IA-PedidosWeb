<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebVentaDetallada extends Model
{
    protected $table = 'pq_pedidosweb_ventadetallada';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCliente::class, 'cod_client', 'cod_client');
    }
}
