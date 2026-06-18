<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PqExcelImportacion extends Model
{
    public $timestamps = false;

    protected $table = 'pq_excel_importaciones';

    protected $fillable = [
        'guid_importacion',
        'id_proceso',
        'usuario_ejecucion',
        'terminal_ejecucion',
        'archivo_original_nombre',
        'archivo_original_extension',
        'hoja_seleccionada',
        'mantener_espacios_en_blanco',
        'mantener_caracteres_especiales',
        'estado_importacion',
        'es_asincronica',
        'fecha_inicio',
        'fecha_fin',
        'cantidad_filas_leidas',
        'cantidad_filas_descartadas',
        'cantidad_filas_validas',
        'cantidad_filas_con_error',
        'cantidad_filas_procesadas',
        'mensaje_resultado',
        'puede_cancelar',
    ];

    protected $casts = [
        'mantener_espacios_en_blanco' => 'boolean',
        'mantener_caracteres_especiales' => 'boolean',
        'es_asincronica' => 'boolean',
        'puede_cancelar' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(PqExcelProceso::class, 'id_proceso');
    }

    public function filas(): HasMany
    {
        return $this->hasMany(PqExcelImportacionFila::class, 'id_importacion');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(PqExcelImportacionNotificacion::class, 'id_importacion');
    }
}
