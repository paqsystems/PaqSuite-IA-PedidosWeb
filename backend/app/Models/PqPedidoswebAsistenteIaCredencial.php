<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PqPedidoswebAsistenteIaCredencial extends Model
{
    protected $table = 'pq_pedidosweb_asistente_ia_credenciales';

    protected $primaryKey = 'id_credencial';

    protected $fillable = [
        'user_id',
        'provider_id',
        'base_url',
        'api_key_encrypted',
        'model_id',
        'supports_vision',
        'is_enabled',
    ];

    protected $casts = [
        'supports_vision' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    protected $hidden = [
        'api_key_encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
