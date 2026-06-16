<?php

namespace App\Services\ExcelImport;

use App\Models\PqExcelImportacion;
use App\Models\PqExcelProceso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessExcelImportLotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $idImportacion,
        private readonly string $tempFilePath,
        private readonly string $hojaSeleccionada,
    ) {}

    public function handle(ExcelImportLotService $lotService): void
    {
        $importacion = PqExcelImportacion::query()->find($this->idImportacion);
        if ($importacion === null) {
            @unlink($this->tempFilePath);

            return;
        }

        $proceso = PqExcelProceso::query()->find($importacion->id_proceso);
        if ($proceso === null) {
            @unlink($this->tempFilePath);

            return;
        }

        try {
            $lotService->processLotFile($importacion, $proceso, $this->tempFilePath, $this->hojaSeleccionada);
        } finally {
            @unlink($this->tempFilePath);
        }
    }
}
