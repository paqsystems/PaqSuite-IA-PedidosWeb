<?php

namespace App\Services\PedidosWeb\Integracion\Export;

use App\Services\PedidosWeb\Integracion\Export\Dto\ExportComprobantesPage;

/**
 * Stub Fase 1 — reemplazado por StoredProcedureExportComprobantesRepository en Fase 2.
 */
final class FakeExportComprobantesRepository implements ExportComprobantesRepositoryInterface
{
    public function list(ExportComprobantesQuery $query): ExportComprobantesPage
    {
        return new ExportComprobantesPage([], $query->page, $query->pageSize, 0);
    }
}