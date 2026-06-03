<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PqPedidoswebArticulo extends Model
{
    protected $table = 'pq_pedidosweb_articulos';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
        'bonificacion',
        'usa_esc',
        'base',
        'valor1',
        'valor2',
        'porc_iva',
    ];

    protected $casts = [
        'bonificacion' => 'decimal:4',
        'usa_esc' => 'boolean',
        'valor1' => 'decimal:4',
        'valor2' => 'decimal:4',
        'porc_iva' => 'decimal:4',
    ];

    public function stock(): HasMany
    {
        return $this->hasMany(PqPedidoswebStock::class, 'cod_articulo', 'codigo');
    }

    /**
     * Valores de escala cuando {@see $usa_esc} es true: {@see $valor1} y {@see $valor2}
     * referencian {@see PqPedidoswebEscalasDetalle::cod_valor}.
     */
    public function escalaDetalleValor1(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebEscalasDetalle::class, 'valor1', 'cod_valor');
    }

    public function escalaDetalleValor2(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebEscalasDetalle::class, 'valor2', 'cod_valor');
    }
}
