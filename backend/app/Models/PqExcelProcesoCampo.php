<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PqExcelProcesoCampo extends Model
{
    public $timestamps = false;

    protected $table = 'pq_excel_procesos_campos';

    protected $fillable = [
        'id_proceso',
        'orden_campo',
        'nombre_columna_excel',
        'nombre_campo_interno',
        'tipo_dato',
        'largo_maximo',
        'cantidad_decimales',
        'es_columna_obligatoria_estructural',
        'es_campo_codigo',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'es_columna_obligatoria_estructural' => 'boolean',
        'es_campo_codigo' => 'boolean',
        'activo' => 'boolean',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(PqExcelProceso::class, 'id_proceso');
    }
}
