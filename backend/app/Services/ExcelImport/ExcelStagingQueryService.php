<?php

namespace App\Services\ExcelImport;

use App\Models\PqExcelImportacion;
use App\Models\PqExcelImportacionFila;
use App\Models\PqExcelProceso;
use App\Models\PqExcelProcesoCampo;

final class ExcelStagingQueryService
{
    /**
     * @return array<string, mixed>
     */
    public function listFilas(PqExcelImportacion $importacion, int $page, int $pageSize, ?bool $soloConError): array
    {
        $query = PqExcelImportacionFila::query()
            ->where('id_importacion', $importacion->id)
            ->orderBy('numero_fila_excel');

        if ($soloConError === true) {
            $query->where('tiene_error', true);
        }

        $total = (clone $query)->count();
        $items = $query
            ->forPage($page, $pageSize)
            ->get()
            ->map(function (PqExcelImportacionFila $fila): array {
                $datos = json_decode((string) $fila->datos_normalizados_json, true);

                return [
                    'idImportacionFila' => (int) $fila->id,
                    'numeroFilaExcel' => (int) $fila->numero_fila_excel,
                    'tieneError' => (bool) $fila->tiene_error,
                    'errorImportacion' => $fila->error_importacion,
                    'estadoFila' => $fila->estado_fila,
                    'datos' => is_array($datos) ? $datos : [],
                ];
            })
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
     * @return list<array<string, mixed>>
     */
    public function listValidRowPayload(PqExcelImportacion $importacion): array
    {
        return PqExcelImportacionFila::query()
            ->where('id_importacion', $importacion->id)
            ->where('tiene_error', false)
            ->orderBy('numero_fila_excel')
            ->get()
            ->map(function (PqExcelImportacionFila $fila): array {
                $datos = json_decode((string) $fila->datos_normalizados_json, true);

                return [
                    'numeroFilaExcel' => (int) $fila->numero_fila_excel,
                    'estadoFila' => $fila->estado_fila,
                    'datos' => is_array($datos) ? $datos : [],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildColumnasMetadata(PqExcelImportacion $importacion, ExcelImportProcessService $processService): array
    {
        $proceso = $importacion->proceso;
        $campos = $proceso?->campos()
            ->where('activo', true)
            ->orderBy('orden_campo')
            ->get() ?? collect();

        $columnas = $campos->map(function (PqExcelProcesoCampo $campo): array {
            return [
                'dataField' => $campo->nombre_campo_interno,
                'caption' => $campo->nombre_columna_excel,
                'tipoDato' => $campo->tipo_dato,
                'format' => $this->resolveFormat($campo),
            ];
        })->values()->all();

        $columnas[] = [
            'dataField' => 'errorImportacion',
            'caption' => 'Errores',
            'tipoDato' => 'texto',
            'format' => null,
            'fixed' => true,
        ];

        $puedeProcesar = $this->canProcessWithoutThrowing($importacion, $processService);

        return [
            'columnas' => $columnas,
            'permiteProcesamientoParcial' => (bool) ($proceso?->permite_procesamiento_parcial ?? false),
            'permiteSoloValidar' => (bool) ($proceso?->permite_solo_validar ?? false),
            'puedeProcesar' => $puedeProcesar,
            'cantidadFilasValidas' => (int) $importacion->cantidad_filas_validas,
            'cantidadFilasConError' => (int) $importacion->cantidad_filas_con_error,
            'estadoImportacion' => $importacion->estado_importacion,
        ];
    }

    private function resolveFormat(PqExcelProcesoCampo $campo): ?string
    {
        return match ($campo->tipo_dato) {
            'decimal' => '#,##0.00',
            'entero' => '#,##0',
            'fecha' => 'dd/MM/yyyy',
            default => null,
        };
    }

    private function canProcessWithoutThrowing(PqExcelImportacion $importacion, ExcelImportProcessService $processService): bool
    {
        try {
            $processService->assertCanProcess($importacion);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
