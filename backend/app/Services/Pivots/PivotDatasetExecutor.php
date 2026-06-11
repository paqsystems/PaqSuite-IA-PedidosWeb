<?php

namespace App\Services\Pivots;

use App\Exceptions\PivotFlowException;
use App\Models\User;
use App\Services\PedidosWeb\HistorialVentasConsultaService;
use App\Support\PivotErrorCodes;

final class PivotDatasetExecutor
{
    private const defaultPageSize = 500;

    private const maxPageSizeCap = 5000;

    public function __construct(
        private readonly PivotMetadataResolver $pivotMetadataResolver,
        private readonly HistorialVentasConsultaService $historialVentasConsultaService,
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
        if ($fuenteTipo === 'service' && $fuenteNombre === 'historial_ventas') {
            $mappedFilters = [
                'page' => $page,
                'page_size' => $pageSize,
            ];

            if (isset($filtros['codCliente']) && $filtros['codCliente'] !== '') {
                $mappedFilters['cod_cliente'] = (string) $filtros['codCliente'];
            }

            $result = $this->historialVentasConsultaService->listar($user, $mappedFilters);

            return [
                'items' => $result['items'],
                'totalRegistros' => (int) ($result['total'] ?? 0),
            ];
        }

        throw new PivotFlowException(
            PivotErrorCodes::metadataInvalid,
            'pivot.datasetSourceUnsupported',
            422
        );
    }
}
