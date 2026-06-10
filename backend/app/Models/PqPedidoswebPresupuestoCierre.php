<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebPresupuestoCierre extends Model
{
    protected $table = 'pq_pedidosweb_presupuestos_cierres';

    protected $primaryKey = 'id_cierre';

    public $timestamps = false;

    protected $fillable = [
        'cod_presupuesto',
        'cod_pedido_generado',
        'tipo_cierre',
        'id_motivo',
        'fecha_cierre',
        'cod_usuario_web',
        'observacion',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
        'id_motivo' => 'integer',
    ];

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebPedidoCabecera::class, 'cod_presupuesto', 'cod_pedido');
    }

    public function pedidoGenerado(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebPedidoCabecera::class, 'cod_pedido_generado', 'cod_pedido');
    }

    public function motivo(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebMotivoCierre::class, 'id_motivo', 'id_motivo');
    }
}
