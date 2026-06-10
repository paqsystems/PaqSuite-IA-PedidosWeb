<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebVentaDetallada extends Model
{
    protected $table = 'pq_pedidosweb_ventadetallada';

    protected $primaryKey = 'id_gva53';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'cod_cli',
        'razon_soci',
        'n_remito',
        't_comp',
        'n_comp',
        'fecha_emi',
        'cond_vta',
        'porc_desc',
        'cotiz',
        'moneda',
        'total_comp',
        'cod_transp',
        'nom_transp',
        'cod_articu',
        'descripcio',
        'cod_dep',
        'um',
        'cantidad',
        'precio',
        'tot_s_imp',
        'n_comp_rem',
        'cant_rem',
        'fecha_rem',
        'fecha_proceso',
        'id_gva53',
    ];

    protected $casts = [
        'fecha_emi' => 'datetime',
        'fecha_rem' => 'datetime',
        'fecha_proceso' => 'datetime',
        'cond_vta' => 'integer',
        'porc_desc' => 'decimal:2',
        'cotiz' => 'decimal:2',
        'total_comp' => 'decimal:2',
        'cantidad' => 'decimal:2',
        'precio' => 'decimal:2',
        'tot_s_imp' => 'decimal:2',
        'cant_rem' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCliente::class, 'cod_cli', 'cod_client');
    }
}
