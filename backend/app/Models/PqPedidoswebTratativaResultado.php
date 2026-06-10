<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PqPedidoswebTratativaResultado extends Model
{
    protected $table = 'pq_pedidosweb_tratativas_resultados';

    protected $primaryKey = 'id_resultado';

    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tratativas(): HasMany
    {
        return $this->hasMany(PqPedidoswebTratativa::class, 'id_resultado', 'id_resultado');
    }
}
