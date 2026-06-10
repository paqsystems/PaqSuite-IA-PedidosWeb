<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use App\Services\PedidosWeb\PedidosWebSchemaBootstrap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebDeuda extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'pq_pedidosweb_deuda';

    protected $primaryKey = 'n_comp';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'fecha_vto' => 'datetime',
        'fecha_proceso' => 'datetime',
        'saldo' => 'decimal:2',
    ];

    protected function getCompositeKeyNames(): array
    {
        $columns = app(PedidosWebSchemaBootstrap::class)->deudaColumnMap();

        return ['cod_cliente', $columns['tipo'], $columns['numero'], 'fecha_vto'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCliente::class, 'cod_cliente', 'cod_client');
    }
}
