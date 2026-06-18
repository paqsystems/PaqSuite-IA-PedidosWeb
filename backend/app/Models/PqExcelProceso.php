<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PqExcelProceso extends Model
{
    public $timestamps = false;

    protected $table = 'pq_excel_procesos';

    protected $fillable = [
        'codigo_proceso',
        'nombre_proceso',
        'descripcion',
        'nombre_hoja_default',
        'permite_procesamiento_parcial',
        'permite_solo_validar',
        'genera_plantilla',
        'mantener_espacios_en_blanco_default',
        'mantener_caracteres_especiales_default',
        'handler_backend',
        'procedimiento_host',
        'formato_booleano_plantilla',
        'activo',
        'usuario_alta',
    ];

    protected $casts = [
        'permite_procesamiento_parcial' => 'boolean',
        'permite_solo_validar' => 'boolean',
        'genera_plantilla' => 'boolean',
        'mantener_espacios_en_blanco_default' => 'boolean',
        'mantener_caracteres_especiales_default' => 'boolean',
        'activo' => 'boolean',
    ];

    public function campos(): HasMany
    {
        return $this->hasMany(PqExcelProcesoCampo::class, 'id_proceso');
    }
}
