<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqRolAtributo extends Model
{
    protected $table = 'PQ_RolAtributo';

    public $timestamps = false;

    protected $fillable = [
        'id_rol',
        'procedimiento',
        'permiso_alta',
        'permiso_baja',
        'permiso_modi',
        'permiso_repo',
    ];

    protected $casts = [
        'permiso_alta' => 'boolean',
        'permiso_baja' => 'boolean',
        'permiso_modi' => 'boolean',
        'permiso_repo' => 'boolean',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(PqRol::class, 'id_rol', 'id');
    }
}
