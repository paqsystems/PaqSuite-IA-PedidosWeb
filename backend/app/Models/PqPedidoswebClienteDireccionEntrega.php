<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebClienteDireccionEntrega extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'pq_pedidosweb_clientesde';

    protected $primaryKey = 'id_de';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_client',
        'id_de',
        'cod_DE',
        'direccion',
        'localidad',
        'c_postal',
        'cod_provin',
        'habitual',
    ];

    protected $casts = [
        'id_de' => 'integer',
        'habitual' => 'boolean',
    ];

    protected function getCompositeKeyNames(): array
    {
        return ['cod_client', 'id_de'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCliente::class, 'cod_client', 'cod_client');
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebProvincia::class, 'cod_provin', 'cod_provin');
    }
}
