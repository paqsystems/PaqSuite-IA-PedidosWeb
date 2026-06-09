<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PqPedidoswebArticulo extends Model
{
    protected $table = 'pq_pedidosweb_articulos';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
        'bonificacion',
        'usa_esc',
        'base',
        'valor1',
        'valor2',
        'porc_iva',
    ];

    protected $casts = [
        'bonificacion' => 'decimal:4',
        'valor1' => 'decimal:4',
        'valor2' => 'decimal:4',
        'porc_iva' => 'decimal:4',
    ];

    public function stock(): HasMany
    {
        return $this->hasMany(PqPedidoswebStock::class, 'cod_articulo', 'codigo');
    }

    /**
     * Lookup de carga: excluye artículos BASE ({@see self::MARCA_USA_ESC_BASE}).
     *
     * En ERP con {@see $usa_esc} alfanumérico aplica la marca {@see MARCA_USA_ESC_BASE}.
     * Cuando la columna es {@code bit} (p. ej. Ankas) los padres de presentaciones se detectan
     * porque otro artículo referencia su {@see $codigo} en {@see $base}.
     */
    public function scopeExcluirArticulosBaseCarga(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->where(function (Builder $builder): void {
                $builder->whereNull('usa_esc')
                    ->orWhereRaw(
                        'UPPER(LTRIM(RTRIM(CAST(usa_esc AS NVARCHAR(20))))) <> ?',
                        [self::MARCA_USA_ESC_BASE],
                    );
            })
            ->whereNotExists(function (QueryBuilder $subquery) use ($table): void {
                $subquery->selectRaw('1')
                    ->from("{$table} as pw_art_presentacion")
                    ->whereRaw("NULLIF(LTRIM(RTRIM(CAST(pw_art_presentacion.[base] AS NVARCHAR(50)))), '') IS NOT NULL")
                    ->whereRaw(
                        'LTRIM(RTRIM(CAST(pw_art_presentacion.[base] AS NVARCHAR(50)))) = LTRIM(RTRIM(CAST('.$table.'.codigo AS NVARCHAR(50))))',
                    )
                    ->whereRaw(
                        'LTRIM(RTRIM(CAST(pw_art_presentacion.codigo AS NVARCHAR(50)))) <> LTRIM(RTRIM(CAST('.$table.'.codigo AS NVARCHAR(50))))',
                    );
            });
    }

    public const MARCA_USA_ESC_BASE = 'B';

    /**
     * Valores de escala cuando {@see $usa_esc} indica escala (no BASE): {@see $valor1} y {@see $valor2}
     * referencian {@see PqPedidoswebEscalasDetalle::cod_valor}. {@see MARCA_USA_ESC_BASE} = artículo BASE (excluido en carga).
     */
    public function escalaDetalleValor1(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebEscalasDetalle::class, 'valor1', 'cod_valor');
    }

    public function escalaDetalleValor2(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebEscalasDetalle::class, 'valor2', 'cod_valor');
    }
}
