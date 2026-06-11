<?php

namespace App\Services\Pivots;

use App\Exceptions\PivotFlowException;
use App\Models\User;
use App\Services\PedidosWeb\ChequesConsultaService;
use App\Services\PedidosWeb\DeudaConsultaService;
use App\Services\PedidosWeb\DetallePedidosConsultaService;
use App\Services\PedidosWeb\HistorialVentasConsultaService;
use App\Services\PedidosWeb\StockConsultaService;
use App\Support\PivotErrorCodes;

final class PivotDatasetExecutor
{
    private const defaultPageSize = 500;

    private const maxPageSizeCap = 5000;

    public function __construct(
        private readonly PivotMetadataResolver $pivotMetadataResolver,
        private readonly HistorialVentasConsultaService $historialVentasConsultaService,
        private readonly DetallePedidosConsultaService $detallePedidosConsultaService,
        private readonly DeudaConsultaService $deudaConsultaService,
        private readonly ChequesConsultaService $chequesConsultaService,
        private readonly StockConsultaService $stockConsultaService,
    ) {}

    /**
     * @param  array<string, mixed>  $filtros
     * @return array<string, mixed>
     */
    public function execute(User $user, string $consultaId, array $filtros, int $pagina, int $tamanoPagina): array
    {
        $consulta = $this->pivotMetadataResolver->findActiveConsulta($consultaId);
        $metadata = $this->pivotMetadataResolver->resolveMetadata($consultaId);

        $this->assertRequiredFilters($metadata['filtrosGenerales'], $filtros);

        $restricciones = is_array($metadata['restricciones']) ? $metadata['restricciones'] : [];
        $maxRegistros = (int) ($restricciones['maximoRegistrosBase'] ?? self::maxPageSizeCap);
        $pageSize = min(
            self::maxPageSizeCap,
            $maxRegistros,
            $tamanoPagina > 0 ? $tamanoPagina : self::defaultPageSize
        );
        $page = max(1, $pagina);

        $result = $this->fetchDataset(
            $user,
            (string) $consulta->fuente_tipo,
            (string) $consulta->fuente_nombre,
            $filtros,
            $page,
            $pageSize
        );

        $totalRegistros = (int) ($result['totalRegistros'] ?? 0);
        $bloquear = (bool) ($restricciones['bloquearSiExcedeVolumen'] ?? true);

        if ($bloquear && $totalRegistros > $maxRegistros) {
            throw new PivotFlowException(
                PivotErrorCodes::volumeExceeded,
                'pivot.volumeExceeded',
                422
            );
        }

        $truncado = $totalRegistros > ($page * $pageSize);

        return [
            'items' => $result['items'],
            'totalRegistros' => $totalRegistros,
            'truncado' => $truncado,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $filtrosGenerales
     * @param  array<string, mixed>  $filtros
     */
    private function assertRequiredFilters(array $filtrosGenerales, array $filtros): void
    {
        foreach ($filtrosGenerales as $filtro) {
            $obligatorio = (bool) ($filtro['obligatorio'] ?? false);

            if (! $obligatorio) {
                continue;
            }

            $dataField = (string) ($filtro['dataField'] ?? $filtro['filtroId'] ?? '');
            $value = $filtros[$dataField] ?? $filtros[$filtro['filtroId'] ?? ''] ?? null;

            if ($value === null || $value === '') {
                throw new PivotFlowException(
                    PivotErrorCodes::requiredFilterMissing,
                    'pivot.requiredFilterMissing',
                    422
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{items: array<int, mixed>, totalRegistros: int}
     */
    private function fetchDataset(
        User $user,
        string $fuenteTipo,
        string $fuenteNombre,
        array $filtros,
        int $page,
        int $pageSize
    ): array {
        if ($fuenteTipo !== 'service') {
            throw new PivotFlowException(
                PivotErrorCodes::metadataInvalid,
                'pivot.datasetSourceUnsupported',
                422
            );
        }

        $mappedFilters = $this->mapConsultaFilters($filtros, $page, $pageSize);

        $result = match ($fuenteNombre) {
            'historial_ventas' => $this->historialVentasConsultaService->listar($user, $mappedFilters),
            'detalle_pedidos' => $this->detallePedidosConsultaService->listar($user, $mappedFilters),
            'deuda' => $this->deudaConsultaService->listar($user, $mappedFilters),
            'cheques' => $this->chequesConsultaService->listar($user, $mappedFilters),
            'stock' => $this->stockConsultaService->listar($mappedFilters),
            default => throw new PivotFlowException(
                PivotErrorCodes::metadataInvalid,
                'pivot.datasetSourceUnsupported',
                422
            ),
        };

        return [
            'items' => $result['items'],
            'totalRegistros' => (int) ($result['total'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array<string, mixed>
     */
    private function mapConsultaFilters(array $filtros, int $page, int $pageSize): array
    {
        $mapped = [
            'page' => $page,
            'page_size' => $pageSize,
        ];

        if (isset($filtros['codCliente']) && $filtros['codCliente'] !== '') {
            $mapped['cod_cliente'] = (string) $filtros['codCliente'];
        }

        if (isset($filtros['codPedido']) && $filtros['codPedido'] !== '') {
            $mapped['cod_pedido'] = (string) $filtros['codPedido'];
        }

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $mapped['estado'] = $filtros['estado'];
        }

        if (isset($filtros['q']) && $filtros['q'] !== '') {
            $mapped['q'] = (string) $filtros['q'];
        }

        return $mapped;
    }
}
