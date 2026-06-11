<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PqPivotConfig extends Model
{
    protected $table = 'pq_pivots_config';

    protected $primaryKey = 'pivot_id';

    protected $fillable = [
        'consulta_id',
        'nombre',
        'configuracion_json',
        'version_definicion_consulta',
        'created_by_user_id',
        'eliminado',
        'activo',
    ];

    protected $casts = [
        'eliminado' => 'boolean',
        'activo' => 'boolean',
        'version_definicion_consulta' => 'integer',
    ];

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
