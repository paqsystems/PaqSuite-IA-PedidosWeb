<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PqPedidoswebListaPrecios extends Model
{
    protected $table = 'pq_pedidosweb_listaprecios';

    protected $primaryKey = 'cod_lista';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_lista',
        'incluye_iva',
        'moneda',
        'descripcion',
        'decimales',
    ];

    protected $casts = [
        'cod_lista' => 'integer',
        'incluye_iva' => 'boolean',
        'moneda' => 'integer',
        'decimales' => 'integer',
    ];

    public function articulos(): HasMany
    {
        return $this->hasMany(PqPedidoswebListaPreciosArticulo::class, 'cod_lista', 'cod_lista');
    }
}
