<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PqPedidoswebMotivoCierre extends Model
{
    protected $table = 'pq_pedidosweb_motivos_cierre';

    protected $primaryKey = 'id_motivo';

    public $timestamps = false;

    protected $fillable = [
        'tipo_cierre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function cierres(): HasMany
    {
        return $this->hasMany(PqPedidoswebPresupuestoCierre::class, 'id_motivo', 'id_motivo');
    }
}
