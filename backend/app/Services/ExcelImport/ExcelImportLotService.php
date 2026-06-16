<?php

namespace App\Services\ExcelImport;

use App\Exceptions\ExcelImportFlowException;
use App\Models\PqExcelImportacion;
use App\Models\PqExcelImportacionFila;
use App\Models\PqExcelImportacionFilaError;
use App\Models\PqExcelImportacionNotificacion;
use App\Models\PqExcelProceso;
use App\Models\User;
use App\Services\ExcelImport\Dto\ExcelImportLotContext;
use App\Services\ExcelImport\Dto\ExcelRowError;
use App\Support\ExcelImportErrorCodes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class ExcelImportLotService
{
    public function __construct(
        private readonly ExcelTemplateService $excelTemplateService,
        private readonly ExcelWorkbookService $workbookService,
        private readonly ExcelImportHandlerRegistry $handlerRegistry,
        private readonly ExcelImportParserService $parserService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function createLotFromUpload(
        User $user,
        string $codigoProceso,
        UploadedFile $archivo,
        string $hojaSeleccionada,
        ?string $terminal = null
    ): array {
        $extension = strtolower($archivo->getClientOriginalExtension());
        if ($extension !== 'xlsx') {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::formatoInvalido,
                'excelImport.formatoInvalido',
                422
            );
        }

        $path = $archivo->getRealPath();
        if ($path === false) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::archivoCorrupto,
                'excelImport.archivoCorrupto',
                422
            );
        }

        $proceso = $this->excelTemplateService->findActiveProceso($codigoProceso);
        $campos = $proceso->campos()->where('activo', true)->orderBy('orden_campo')->get();

        $estimatedRows = 0;
        try {
            $spreadsheet = $this->workbookService->loadWorkbook($path);
            $sheet = $this->workbookService->resolveWorksheet($spreadsheet, $hojaSeleccionada);
            $estimatedRows = $this->workbookService->estimateDataRowCount($sheet);
        } catch (ExcelImportFlowException $exception) {
            throw $exception;
        }

        $esAsincronica = $this->shouldRunAsync($archivo->getSize(), $estimatedRows);

        $importacion = PqExcelImportacion::query()->create([
            'guid_importacion' => (string) Str::uuid(),
            'id_proceso' => $proceso->id,
            'usuario_ejecucion' => (string) $user->codigo,
            'terminal_ejecucion' => $terminal,
            'archivo_original_nombre' => $archivo->getClientOriginalName(),
            'archivo_original_extension' => '.xlsx',
            'hoja_seleccionada' => $hojaSeleccionada,
            'mantener_espacios_en_blanco' => (bool) $proceso->mantener_espacios_en_blanco_default,
            'mantener_caracteres_especiales' => (bool) $proceso->mantener_caracteres_especiales_default,
            'estado_importacion' => $esAsincronica ? 'validando' : 'validando',
            'es_asincronica' => $esAsincronica,
            'fecha_inicio' => now(),
            'puede_cancelar' => true,
        ]);

        if ($esAsincronica) {
            $tempPath = $this->storeTempCopy($path);
            ProcessExcelImportLotJob::dispatch(
                $importacion->id,
                $tempPath,
                $hojaSeleccionada
            );

            return $this->buildLotResponse($importacion->fresh());
        }

        $this->processLotFile($importacion, $proceso, $path, $hojaSeleccionada, $campos);

        return $this->buildLotResponse($importacion->fresh());
    }

    public function processLotFile(
        PqExcelImportacion $importacion,
        PqExcelProceso $proceso,
        string $path,
        string $hojaSeleccionada,
        $campos = null
    ): void {
        $campos ??= $proceso->campos()->where('activo', true)->orderBy('orden_campo')->get();
        $spreadsheet = $this->workbookService->loadWorkbook($path);
        $sheet = $this->workbookService->resolveWorksheet($spreadsheet, $hojaSeleccionada);

        $handler = $this->handlerRegistry->resolve($proceso->handler_backend);
        $ctx = new ExcelImportLotContext(
            (int) $importacion->id,
            (string) $importacion->guid_importacion,
            (int) $proceso->id,
            (string) $proceso->codigo_proceso,
            (string) $importacion->usuario_ejecucion
        );

        $parsed = $this->parserService->parseSheet(
            $sheet,
            $proceso,
            $campos,
            (bool) $importacion->mantener_espacios_en_blanco,
            (bool) $importacion->mantener_caracteres_especiales,
            $handler,
            $ctx
        );

        DB::transaction(function () use ($importacion, $parsed): void {
            if ($parsed['structuralError'] !== null) {
                $importacion->update([
                    'estado_importacion' => 'con_error_estructura',
                    'mensaje_resultado' => $parsed['structuralError'],
                    'puede_cancelar' => false,
                    'fecha_fin' => now(),
                ]);

                return;
            }

            foreach ($parsed['rows'] as $row) {
                $fila = PqExcelImportacionFila::query()->create([
                    'id_importacion' => $importacion->id,
                    'numero_fila_excel' => $row['numeroFilaExcel'],
                    'estado_fila' => $row['estadoFila'],
                    'fila_ajustada_automaticamente' => $row['filaAjustada'],
                    'tiene_error' => $row['tieneError'],
                    'error_importacion' => $row['errorImportacion'],
                    'datos_originales_json' => json_encode($row['datosOriginales'], JSON_UNESCAPED_UNICODE),
                    'datos_normalizados_json' => json_encode($row['datosNormalizados'], JSON_UNESCAPED_UNICODE),
                    'fecha_alta' => now(),
                ]);

                $secuencia = 1;
                foreach ($row['errores'] as $error) {
                    /** @var ExcelRowError $error */
                    PqExcelImportacionFilaError::query()->create([
                        'id_importacion_fila' => $fila->id,
                        'secuencia_error' => $secuencia++,
                        'codigo_error' => $error->codigoError,
                        'tipo_error' => $error->tipoError,
                        'nombre_campo_interno' => $error->nombreCampoInterno,
                        'nombre_columna_excel' => $error->nombreColumnaExcel,
                        'mensaje_error' => $error->mensajeError,
                    ]);
                }
            }

            $importacion->update([
                'estado_importacion' => 'lista_para_procesar',
                'cantidad_filas_leidas' => $parsed['leidas'],
                'cantidad_filas_descartadas' => $parsed['descartadas'],
                'cantidad_filas_validas' => $parsed['validas'],
                'cantidad_filas_con_error' => $parsed['conError'],
                'puede_cancelar' => true,
            ]);
        });

        if ($importacion->es_asincronica) {
            $this->notifyAsyncComplete($importacion->fresh());
        }
    }

    public function cancelLot(PqExcelImportacion $importacion, User $user): void
    {
        if ((string) $importacion->usuario_ejecucion !== (string) $user->codigo) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::loteNoCancelable,
                'excelImport.loteNoCancelable',
                403
            );
        }

        if (! $importacion->puede_cancelar || in_array($importacion->estado_importacion, ['procesando', 'procesada', 'procesada_parcial', 'cancelada'], true)) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::loteNoCancelable,
                'excelImport.loteNoCancelable',
                422
            );
        }

        $importacion->update([
            'estado_importacion' => 'cancelada',
            'puede_cancelar' => false,
            'fecha_fin' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildLotDetail(PqExcelImportacion $importacion): array
    {
        $proceso = $importacion->proceso;

        return [
            'guidImportacion' => $importacion->guid_importacion,
            'codigoProceso' => $proceso?->codigo_proceso,
            'nombreProceso' => $proceso?->nombre_proceso,
            'estadoImportacion' => $importacion->estado_importacion,
            'esAsincronica' => (bool) $importacion->es_asincronica,
            'archivoOriginalNombre' => $importacion->archivo_original_nombre,
            'hojaSeleccionada' => $importacion->hoja_seleccionada,
            'cantidadFilasLeidas' => (int) $importacion->cantidad_filas_leidas,
            'cantidadFilasDescartadas' => (int) $importacion->cantidad_filas_descartadas,
            'cantidadFilasValidas' => (int) $importacion->cantidad_filas_validas,
            'cantidadFilasConError' => (int) $importacion->cantidad_filas_con_error,
            'cantidadFilasProcesadas' => (int) $importacion->cantidad_filas_procesadas,
            'permiteProcesamientoParcial' => (bool) ($proceso?->permite_procesamiento_parcial ?? false),
            'permiteSoloValidar' => (bool) ($proceso?->permite_solo_validar ?? false),
            'puedeCancelar' => (bool) $importacion->puede_cancelar,
            'mensajeResultado' => $importacion->mensaje_resultado,
            'fechaInicio' => $importacion->fecha_inicio?->toIso8601String(),
            'fechaFin' => $importacion->fecha_fin?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildLotResponse(PqExcelImportacion $importacion): array
    {
        $proceso = $importacion->proceso;

        return [
            'guidImportacion' => $importacion->guid_importacion,
            'codigoProceso' => $proceso?->codigo_proceso,
            'estadoImportacion' => $importacion->estado_importacion,
            'esAsincronica' => (bool) $importacion->es_asincronica,
            'archivoOriginalNombre' => $importacion->archivo_original_nombre,
            'hojaSeleccionada' => $importacion->hoja_seleccionada,
            'cantidadFilasLeidas' => (int) $importacion->cantidad_filas_leidas,
            'cantidadFilasDescartadas' => (int) $importacion->cantidad_filas_descartadas,
            'cantidadFilasValidas' => (int) $importacion->cantidad_filas_validas,
            'cantidadFilasConError' => (int) $importacion->cantidad_filas_con_error,
            'permiteProcesamientoParcial' => (bool) ($proceso?->permite_procesamiento_parcial ?? false),
            'permiteSoloValidar' => (bool) ($proceso?->permite_solo_validar ?? false),
            'mensajeResultado' => $importacion->mensaje_resultado,
        ];
    }

    private function shouldRunAsync(int $bytes, int $estimatedRows): bool
    {
        return $bytes > (int) config('excel_import.asyncMaxBytes')
            || $estimatedRows > (int) config('excel_import.asyncMaxEstimatedRows');
    }

    private function storeTempCopy(string $sourcePath): string
    {
        $dest = tempnam(sys_get_temp_dir(), 'excel_import_');
        if ($dest === false) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::archivoCorrupto,
                'excelImport.archivoCorrupto',
                422
            );
        }

        copy($sourcePath, $dest);

        return $dest;
    }

    private function notifyAsyncComplete(PqExcelImportacion $importacion): void
    {
        PqExcelImportacionNotificacion::query()->create([
            'id_importacion' => $importacion->id,
            'usuario_destino' => $importacion->usuario_ejecucion,
            'tipo_notificacion' => 'toast',
            'fecha_generacion' => now(),
            'titulo' => 'excelImport.notif.cargaTitulo',
            'mensaje' => 'excelImport.notif.cargaMensaje',
            'leida' => false,
        ]);
    }
}
