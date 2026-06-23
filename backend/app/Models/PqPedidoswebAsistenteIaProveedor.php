<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PqPedidoswebAsistenteIaProveedor extends Model
{
    protected $table = 'pq_pedidosweb_asistente_ia_proveedores';

    protected $primaryKey = 'id_proveedor';

    public $timestamps = false;

    protected $fillable = [
        'provider_id',
        'nombre_visible',
        'tipo_integracion',
        'soporta_byok',
        'soporta_imagenes',
        'requiere_base_url_editable',
        'url_documentacion',
        'url_onboarding',
        'activo',
        'observacion',
    ];

    protected $casts = [
        'soporta_byok' => 'boolean',
        'soporta_imagenes' => 'boolean',
        'requiere_base_url_editable' => 'boolean',
        'activo' => 'boolean',
    ];
}
