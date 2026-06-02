<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebDeuda extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'pq_pedidosweb_deuda';

    protected $primaryKey = 'nro_comprobante';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_cliente',
        'tipo_comprobante',
        'nro_comprobante',
        'fecha_vto',
        'fecha',
        'fecha_proceso',
        'saldo',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_vto' => 'datetime',
        'fecha_proceso' => 'datetime',
        'saldo' => 'decimal:2',
    ];

    protected function getCompositeKeyNames(): array
    {
        return ['cod_cliente', 'tipo_comprobante', 'nro_comprobante', 'fecha_vto'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCliente::class, 'cod_cliente', 'cod_client');
    }
}
