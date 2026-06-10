<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PqRol extends Model
{
    protected $table = 'Pq_Rol';

    public $timestamps = false;

    protected $fillable = [
        'nombre_rol',
        'descripcion_rol',
        'acceso_total',
    ];

    protected $casts = [
        'acceso_total' => 'boolean',
    ];

    public function permisos(): HasMany
    {
        return $this->hasMany(PqPermiso::class, 'id_rol', 'id');
    }

    public function atributos(): HasMany
    {
        return $this->hasMany(PqRolAtributo::class, 'id_rol', 'id');
    }
}
