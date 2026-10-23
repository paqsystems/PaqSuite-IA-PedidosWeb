<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqPedidoswebClienteDireccionEntrega extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'pq_pedidosweb_clientesde';

    protected $primaryKey = 'id_de';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_client',
        'id_de',
        'cod_DE',
        'direccion',
        'localidad',
        'c_postal',
        'cod_provin',
        'habitual',
    ];

    protected $casts = [
        'id_de' => 'integer',
    ];

    /**
     * Columna canónica: {@code char(1)} (`S`/`N`, también tolera `1`/`0`/`Y`).
     * En PHP/API se expone como boolean.
     */
    protected function habitual(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value): bool => self::isHabitualFlag($value),
            set: static fn (mixed $value): string => self::normalizeHabitualFlag($value),
        );
    }

    public static function isHabitualFlag(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        $normalized = strtoupper(trim((string) $value));

        return in_array($normalized, ['1', 'S', 'Y', 'T', 'TRUE'], true);
    }

    public static function normalizeHabitualFlag(mixed $value): string
    {
        return self::isHabitualFlag($value) ? 'S' : 'N';
    }

    protected function getCompositeKeyNames(): array
    {
        return ['cod_client', 'id_de'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebCliente::class, 'cod_client', 'cod_client');
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(PqPedidoswebProvincia::class, 'cod_provin', 'cod_provin');
    }
}
