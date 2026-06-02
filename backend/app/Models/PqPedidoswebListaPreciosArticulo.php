<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebListaPreciosArticulo extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'pq_pedidosweb_listaprecios_articulos';

    protected $primaryKey = 'cod_articulo';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_lista',
        'cod_articulo',
        'precio',
    ];

    protected $casts = [
        'cod_lista' => 'integer',
        'precio' => 'decimal:4',
    ];

    protected function getCompositeKeyNames(): array
    {
        return ['cod_lista', 'cod_articulo'];
    }

    public function listaPrecios(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebListaPrecios::class, 'cod_lista', 'cod_lista');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebArticulo::class, 'cod_articulo', 'codigo');
    }
}
