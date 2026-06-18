<?php

namespace App\Services\ExcelImport;

use App\Models\PqExcelImportacion;
use App\Models\PqExcelProceso;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class ExcelImportHistoryService
{
    public function __construct(
        private readonly ExcelImportAccessService $accessService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listHistorial(User $user, array $filters, int $page, int $pageSize): array
    {
        $allowedProcesoIds = $this->accessService->resolveAllowedProcesoIdsForHistorial($user);

        $query = PqExcelImportacion::query()
            ->with('proceso')
            ->whereIn('id_proceso', $allowedProcesoIds)
            ->orderByDesc('fecha_inicio');

        if (! empty($filters['codigoProceso'])) {
            $procesoId = PqExcelProceso::query()
                ->where('codigo_proceso', $filters['codigoProceso'])
                ->value('id');
            if ($procesoId !== null) {
                $query->where('id_proceso', $procesoId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (! empty($filters['estadoImportacion'])) {
            $query->where('estado_importacion', $filters['estadoImportacion']);
        }

        if (! empty($filters['usuarioEjecucion'])) {
            $query->where('usuario_ejecucion', $filters['usuarioEjecucion']);
        }

        if (! empty($filters['fechaDesde'])) {
            $query->whereDate('fecha_inicio', '>=', $filters['fechaDesde']);
        }

        if (! empty($filters['fechaHasta'])) {
            $query->whereDate('fecha_inicio', '<=', $filters['fechaHasta']);
        }

        $total = (clone $query)->count();
        $items = $query
            ->forPage($page, $pageSize)
            ->get()
            ->map(fn (PqExcelImportacion $row): array => $this->mapHistorialRow($row))
            ->values()
            ->all();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapHistorialRow(PqExcelImportacion $row): array
    {
        return [
            'guidImportacion' => $row->guid_importacion,
            'codigoProceso' => $row->proceso?->codigo_proceso,
            'nombreProceso' => $row->proceso?->nombre_proceso,
            'usuarioEjecucion' => $row->usuario_ejecucion,
            'archivoOriginalNombre' => $row->archivo_original_nombre,
            'hojaSeleccionada' => $row->hoja_seleccionada,
            'estadoImportacion' => $row->estado_importacion,
            'fechaInicio' => $row->fecha_inicio?->toIso8601String(),
            'fechaFin' => $row->fecha_fin?->toIso8601String(),
            'cantidadFilasLeidas' => (int) $row->cantidad_filas_leidas,
            'cantidadFilasValidas' => (int) $row->cantidad_filas_validas,
            'cantidadFilasConError' => (int) $row->cantidad_filas_con_error,
            'cantidadFilasProcesadas' => (int) $row->cantidad_filas_procesadas,
            'cantidadFilasDescartadas' => (int) $row->cantidad_filas_descartadas,
        ];
    }
}
