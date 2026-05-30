<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPermiso extends Model
{
    protected $table = 'Pq_Permiso';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_rol',
        'id_empresa',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(PqRol::class, 'id_rol', 'id');
    }
}
