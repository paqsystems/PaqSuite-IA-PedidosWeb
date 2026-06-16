<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PqExcelImportacionFila extends Model
{
    public $timestamps = false;

    protected $table = 'pq_excel_importaciones_filas';

    protected $fillable = [
        'id_importacion',
        'numero_fila_excel',
        'estado_fila',
        'fila_ajustada_automaticamente',
        'tiene_error',
        'error_importacion',
        'datos_originales_json',
        'datos_normalizados_json',
        'fecha_alta',
    ];

    protected $casts = [
        'fila_ajustada_automaticamente' => 'boolean',
        'tiene_error' => 'boolean',
        'fecha_alta' => 'datetime',
    ];

    public function importacion(): BelongsTo
    {
        return $this->belongsTo(PqExcelImportacion::class, 'id_importacion');
    }

    public function errores(): HasMany
    {
        return $this->hasMany(PqExcelImportacionFilaError::class, 'id_importacion_fila');
    }
}
