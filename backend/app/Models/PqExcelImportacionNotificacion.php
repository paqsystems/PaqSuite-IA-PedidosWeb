<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PqExcelImportacionNotificacion extends Model
{
    public $timestamps = false;

    protected $table = 'pq_excel_importaciones_notificaciones';

    protected $fillable = [
        'id_importacion',
        'usuario_destino',
        'tipo_notificacion',
        'fecha_generacion',
        'fecha_leida',
        'titulo',
        'mensaje',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'fecha_generacion' => 'datetime',
        'fecha_leida' => 'datetime',
    ];

    public function importacion(): BelongsTo
    {
        return $this->belongsTo(PqExcelImportacion::class, 'id_importacion');
    }
}
