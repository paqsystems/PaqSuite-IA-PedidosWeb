<?php

namespace App\Services\PedidosWeb\Integracion\Export;

use App\Services\PedidosWeb\Integracion\Export\Dto\ExportComprobantesPage;

interface ExportComprobantesRepositoryInterface
{
    public function list(ExportComprobantesQuery $query): ExportComprobantesPage;
}