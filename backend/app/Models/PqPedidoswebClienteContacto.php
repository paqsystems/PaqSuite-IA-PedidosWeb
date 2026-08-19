<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebClienteContacto extends Model
{
    protected $table = 'pq_pedidosweb_clientescontactos';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_client',
        'cod_contacto',
        'nombre',
        'telefono',
        'mail',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCliente::class, 'cod_client', 'cod_client');
    }
}
