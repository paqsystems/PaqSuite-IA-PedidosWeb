<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'nivel',
        'observaciones',
        'incluye_iva',
        'moneda',
        'estado',
        'tal_pedido_tango',
        'nro_pedido_tango',
        'cod_usuario_web',
        'fecha_modif',
        'total',
        'total_iva',
        'leyenda_1',
        'leyenda_2',
        'leyenda_3',
        'leyenda_4',
        'leyenda_5',
        'descuento',
        'bonif_1',
        'bonif_2',
        'bonif_3',
        'cod_perfil',
        'cod_vended',
        'cod_condvta',
        'id_de',
        'cod_transpor',
        'lista_precios',
        'expreso',
        'expreso_dire',
        'fecha_entrega',
        'nro_visible',
        'usuario_creacion',
        'fecha_creacion',
        'usuario_modificacion',
        'fechahora_inicio_proceso',
        'fechahora_ultima_actividad',
        'origen_comprobante',
        'cod_pedido_origen',
        'cod_presupuesto_origen',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_modif' => 'datetime',
        'fecha_entrega' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fechahora_inicio_proceso' => 'datetime',
        'fechahora_ultima_actividad' => 'datetime',
        'estado' => 'integer',
        'nivel' => 'integer',
        'incluye_iva' => 'boolean',
        'moneda' => 'integer',
        'tal_pedido_tango' => 'integer',
        'cod_condvta' => 'integer',
        'id_de' => 'integer',
        'lista_precios' => 'integer',
        'nro_visible' => 'integer',
        'total' => 'decimal:2',
        'total_iva' => 'decimal:2',
        'descuento' => 'decimal:4',
        'bonif_1' => 'decimal:4',
        'bonif_2' => 'decimal:4',
        'bonif_3' => 'decimal:4',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(PqPedidoswebPedidoDetalle::class, 'cod_pedido', 'cod_pedido');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCliente::class, 'cod_cliente', 'cod_client');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebVendedor::class, 'cod_vended', 'cod_vended');
    }

    public function condicionVenta(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCondicionVenta::class, 'cod_condvta', 'codigo');
    }

    public function transporte(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebTransporte::class, 'cod_transpor', 'codigo');
    }

    public function listaPrecios(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebListaPrecios::class, 'lista_precios', 'cod_lista');
    }

    public function presupuestoCierre(): HasOne
    {
        return $this->hasOne(PqPedidoswebPresupuestoCierre::class, 'cod_presupuesto', 'cod_pedido');
    }
}
