<?php

namespace App\Services\PedidosWeb\Integracion\Export;

use Illuminate\Support\Carbon;

final class ExportComprobantesQuery
{
    /**
     * @param  list<array{desde: int, hasta: int}>  $estadoRangoPairs
     */
    public function __construct(
        public readonly array $estadoRangoPairs,
        public readonly int $page,
        public readonly int $pageSize,
        public readonly ?string $codCliente,
        public readonly ?string $codPedido,
        public readonly ?Carbon $fechaDesde,
        public readonly ?Carbon $fechaHasta,
    ) {}
}