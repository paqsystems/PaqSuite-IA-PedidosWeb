<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebCheque extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'pq_pedidosweb_cheques';

    protected $primaryKey = 'numero';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'interno',
        'numero',
        'cod_client',
        'banco',
        'importe',
        'fecha',
        'origen',
        'estado',
        'fecha_proceso',
    ];

    protected $casts = [
        'importe' => 'decimal:2',
        'fecha' => 'datetime',
        'fecha_proceso' => 'datetime',
    ];

    protected function getCompositeKeyNames(): array
    {
        return ['interno', 'numero'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCliente::class, 'cod_client', 'cod_client');
    }
}
