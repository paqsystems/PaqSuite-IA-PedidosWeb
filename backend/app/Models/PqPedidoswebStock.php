<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebStock extends Model
{
    protected $table = 'pq_pedidosweb_stock';

    protected $primaryKey = 'cod_articulo';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_articulo',
        'stock',
        'comprometido',
        'uma_fecha',
    ];

    protected $casts = [
        'stock' => 'decimal:4',
        'comprometido' => 'decimal:4',
        'uma_fecha' => 'datetime',
    ];

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebArticulo::class, 'cod_articulo', 'codigo');
    }
}
