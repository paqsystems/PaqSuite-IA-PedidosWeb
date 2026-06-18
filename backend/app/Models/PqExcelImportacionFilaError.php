<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PqExcelImportacionFilaError extends Model
{
    public $timestamps = false;

    protected $table = 'pq_excel_importaciones_filas_errores';

    protected $fillable = [
        'id_importacion_fila',
        'secuencia_error',
        'codigo_error',
        'tipo_error',
        'nombre_campo_interno',
        'nombre_columna_excel',
        'mensaje_error',
    ];

    public function fila(): BelongsTo
    {
        return $this->belongsTo(PqExcelImportacionFila::class, 'id_importacion_fila');
    }
}
