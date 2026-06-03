<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebEscalasDetalle extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'pq_pedidosweb_escalas_detalle';

    protected $primaryKey = 'cod_valor';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_escala',
        'cod_valor',
        'desc_valor',
    ];

    protected function getCompositeKeyNames(): array
    {
        return ['cod_escala', 'cod_valor'];
    }

    public function cabecera(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebEscalasCabecera::class, 'cod_escala', 'cod_escala');
    }
}
