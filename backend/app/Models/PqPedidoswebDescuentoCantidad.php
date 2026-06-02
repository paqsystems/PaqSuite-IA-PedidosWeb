<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebDescuentoCantidad extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'pq_pedidosweb_descuentocantidad';

    protected $primaryKey = 'cantidad';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_articu',
        'cantidad',
        'descuento',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'descuento' => 'decimal:4',
    ];

    protected function getCompositeKeyNames(): array
    {
        return ['cod_articu', 'cantidad'];
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebArticulo::class, 'cod_articu', 'codigo');
    }
}
