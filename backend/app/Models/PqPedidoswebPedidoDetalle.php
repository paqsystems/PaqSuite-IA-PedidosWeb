<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebPedidoDetalle extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'pq_pedidosweb_pedidosdetalle';

    protected $primaryKey = 'renglon';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_pedido',
        'renglon',
        'cod_articulo',
        'cantidad',
        'porc_bonif',
        'precio',
        'precio_neto',
        'precio_bruto',
        'porc_iva',
        'iva',
        'descripcion_articulo',
        'importe_lista',
        'importe_neto',
        'importe_total',
        'descuento_origen',
        'precio_origen',
    ];

    protected $casts = [
        'renglon' => 'integer',
        'cantidad' => 'decimal:4',
        'porc_bonif' => 'decimal:4',
        'precio' => 'decimal:4',
        'precio_neto' => 'decimal:4',
        'precio_bruto' => 'decimal:4',
        'porc_iva' => 'decimal:4',
        'iva' => 'decimal:2',
        'importe_lista' => 'decimal:2',
        'importe_neto' => 'decimal:2',
        'importe_total' => 'decimal:2',
    ];

    protected function getCompositeKeyNames(): array
    {
        return ['cod_pedido', 'renglon'];
    }

    public function cabecera(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebPedidoCabecera::class, 'cod_pedido', 'cod_pedido');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebArticulo::class, 'cod_articulo', 'codigo');
    }
}
