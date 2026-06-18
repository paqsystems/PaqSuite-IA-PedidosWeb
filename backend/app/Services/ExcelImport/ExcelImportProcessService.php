<?php

namespace App\Services\ExcelImport;

use App\Exceptions\ExcelImportFlowException;
use App\Models\PqExcelImportacion;
use App\Models\PqExcelImportacionFila;
use App\Models\PqExcelImportacionNotificacion;
use App\Services\ExcelImport\Dto\ExcelImportLotContext;
use App\Support\ExcelImportErrorCodes;
use Illuminate\Support\Facades\DB;

final class ExcelImportProcessService
{
    public function __construct(
        private readonly ExcelImportHandlerRegistry $handlerRegistry,
    ) {}

    public function assertCanProcess(PqExcelImportacion $importacion): void
    {
        $proceso = $importacion->proceso;
        if ($proceso === null) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::loteNotFound,
                'excelImport.loteNotFound',
                404
            );
        }

        if ((bool) $proceso->permite_solo_validar) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::processNotAllowed,
                'excelImport.processNotAllowed',
                422
            );
        }

        if (! in_array($importacion->estado_importacion, ['lista_para_procesar', 'validada'], true)) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::processNotAllowed,
                'excelImport.processNotAllowed',
                422
            );
        }

        $validas = (int) $importacion->cantidad_filas_validas;
        $errores = (int) $importacion->cantidad_filas_con_error;

        if ($validas === 0) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::sinFilasParaProcesar,
                'excelImport.sinFilasParaProcesar',
                422
            );
        }

        if ($validas === 0 && $errores > 0) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::processNotAllowed,
                'excelImport.processNotAllowed',
                422
            );
        }

        if (! $proceso->permite_procesamiento_parcial && $errores >= 1) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::processNotAllowed,
                'excelImport.processBlockedByErrors',
                422
            );
        }

        if ($validas === 0) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::processNotAllowed,
                'excelImport.processNotAllowed',
                422
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function processLot(PqExcelImportacion $importacion): array
    {
        $this->assertCanProcess($importacion);
        $proceso = $importacion->proceso;
        $handler = $this->handlerRegistry->resolve($proceso?->handler_backend);
        $ctx = new ExcelImportLotContext(
            (int) $importacion->id,
            (string) $importacion->guid_importacion,
            (int) $proceso->id,
            (string) $proceso->codigo_proceso,
            (string) $importacion->usuario_ejecucion
        );

        $importacion->update(['estado_importacion' => 'procesando', 'puede_cancelar' => false]);

        $procesadas = 0;
        $omitidas = 0;

        $filas = PqExcelImportacionFila::query()
            ->where('id_importacion', $importacion->id)
            ->orderBy('numero_fila_excel')
            ->get();

        foreach ($filas as $fila) {
            if ($fila->tiene_error) {
                $omitidas++;

                continue;
            }

            $datos = json_decode((string) $fila->datos_normalizados_json, true);
            if (! is_array($datos)) {
                $datos = [];
            }

            try {
                DB::transaction(function () use ($handler, $datos, $ctx, $fila): void {
                    $enriched = $handler->processRow($datos, $ctx);
                    $fila->update([
                        'estado_fila' => 'procesada',
                        'datos_normalizados_json' => json_encode($enriched, JSON_UNESCAPED_UNICODE),
                    ]);
                });
                $procesadas++;
            } catch (\Throwable) {
                $fila->update([
                    'estado_fila' => 'rechazada',
                    'tiene_error' => true,
                    'error_importacion' => trim(((string) $fila->error_importacion).' Sistema: error al procesar'),
                ]);
                $omitidas++;
            }
        }

        $estadoFinal = $this->resolveFinalState($importacion, $procesadas, $omitidas);
        $importacion->update([
            'estado_importacion' => $estadoFinal,
            'cantidad_filas_procesadas' => $procesadas,
            'fecha_fin' => now(),
            'mensaje_resultado' => 'excelImport.processSuccess',
        ]);

        PqExcelImportacionNotificacion::query()->create([
            'id_importacion' => $importacion->id,
            'usuario_destino' => $importacion->usuario_ejecucion,
            'tipo_notificacion' => 'toast',
            'fecha_generacion' => now(),
            'titulo' => 'excelImport.notif.procesoTitulo',
            'mensaje' => 'excelImport.notif.procesoMensaje',
            'leida' => false,
        ]);

        return [
            'estadoImportacion' => $estadoFinal,
            'cantidadFilasProcesadas' => $procesadas,
            'cantidadFilasOmitidas' => $omitidas,
        ];
    }

    private function resolveFinalState(PqExcelImportacion $importacion, int $procesadas, int $omitidas): string
    {
        if ($omitidas > 0 && $procesadas > 0) {
            return 'procesada_parcial';
        }

        if ($procesadas > 0 && (int) $importacion->cantidad_filas_con_error === 0) {
            return 'procesada';
        }

        if ($procesadas > 0) {
            return 'procesada_parcial';
        }

        return 'procesada_parcial';
    }
}
