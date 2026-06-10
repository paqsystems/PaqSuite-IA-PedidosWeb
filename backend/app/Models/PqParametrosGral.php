<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqParametrosGral extends Model
{
    protected $table = 'PQ_parametros_gral';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'Programa',
        'Clave',
        'tipo_valor',
        'Valor_String',
        'Valor_Text',
        'Valor_Int',
        'Valor_DateTime',
        'Valor_Bool',
        'Valor_Decimal',
    ];

    protected $casts = [
        'Valor_Bool' => 'boolean',
        'Valor_Int' => 'integer',
        'Valor_Decimal' => 'float',
        'Valor_DateTime' => 'datetime',
    ];
}
