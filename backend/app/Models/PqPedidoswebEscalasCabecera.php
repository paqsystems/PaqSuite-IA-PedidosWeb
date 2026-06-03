<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PqPedidoswebEscalasCabecera extends Model
{
    protected $table = 'pq_pedidosweb_escalas_cabecera';

    protected $primaryKey = 'cod_escala';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cod_escala',
        'descrip_es',
        'nro_escala',
    ];

    protected $casts = [
        'nro_escala' => 'integer',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(PqPedidoswebEscalasDetalle::class, 'cod_escala', 'cod_escala');
    }
}
