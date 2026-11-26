<?php

namespace App\Services\PedidosWeb\Integracion\Export;

use App\Services\PedidosWeb\Integracion\Export\Dto\ExportComprobantesPage;
use App\Support\PedidosWeb\SqlServerStoredProcedureMultiResultReader;
use Illuminate\Support\Facades\Log;

final class StoredProcedureExportComprobantesRepository implements ExportComprobantesRepositoryInterface
{
    private const PROCEDURE = 'pq_sp_integracion_export_comprobantes_list';

    public function __construct(
        private readonly IntegracionExportComprobanteMapper $mapper,
        private readonly SqlServerStoredProcedureMultiResultReader $reader,
    ) {}

    public function list(ExportComprobantesQuery $query): ExportComprobantesPage
    {
        $json = json_encode($query->estadoRangoPairs, JSON_THROW_ON_ERROR);

        $fechaDesde = $query->fechaDesde?->format('Y-m-d H:i:s.v');
        $fechaHasta = $query->fechaHasta?->format('Y-m-d H:i:s.v');

        $params = [
            $json,
            $query->page,
            $query->pageSize,
            $query->codCliente,
            $query->codPedido,
            $fechaDesde,
            $fechaHasta,
            0,
        ];

        try {
            $result = $this->reader->execute(self::PROCEDURE, $params, [8]);
        } catch (\Throwable $throwable) {
            Log::error('integracion.export.sp_failed', ['message' => $throwable->getMessage()]);

            throw $throwable;
        }

        $total = (int) ($result['outputs'][8] ?? 0);
        $cabeceraRows = $result['resultSets'][0] ?? [];
        $renglonRows = $result['resultSets'][1] ?? [];

        $items = $this->mapper->assembleItems($cabeceraRows, $renglonRows);

        return new ExportComprobantesPage($items, $query->page, $query->pageSize, $total);
    }
}