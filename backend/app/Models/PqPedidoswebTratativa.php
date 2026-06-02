<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PqPedidoswebTratativa extends Model
{
    protected $table = 'pq_pedidosweb_tratativas';

    protected $primaryKey = 'id_tratativa';

    public $timestamps = true;

    protected $fillable = [
        'cod_pedido',
        'fecha_hora',
        'cod_usuario_web',
        'comentario',
        'id_resultado',
        'proxima_fecha',
        'proxima_accion',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'proxima_fecha' => 'datetime',
        'id_resultado' => 'integer',
    ];

    public function cabecera(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebPedidoCabecera::class, 'cod_pedido', 'cod_pedido');
    }

    public function resultado(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebTratativaResultado::class, 'id_resultado', 'id_resultado');
    }
}
