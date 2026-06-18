<?php

namespace App\Services\ExcelImport;

use App\Exceptions\AuthFlowException;
use App\Exceptions\ExcelImportFlowException;
use App\Models\PqExcelImportacion;
use App\Models\PqExcelProceso;
use App\Models\User;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\ExcelImportErrorCodes;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;

final class ExcelImportAccessService
{
    public function __construct(
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    public function ensureEpicEnabled(): ?JsonResponse
    {
        if (! (bool) config('paqsuite_mvp.excelImportEnabled')) {
            return ApiResponse::error(
                ExcelImportErrorCodes::epicDisabled,
                'excelImport.epicDisabled',
                404
            );
        }

        return null;
    }

    public function findLoteByGuid(string $guidImportacion): PqExcelImportacion
    {
        $importacion = PqExcelImportacion::query()
            ->with('proceso')
            ->where('guid_importacion', $guidImportacion)
            ->first();

        if ($importacion === null) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::loteNotFound,
                'excelImport.loteNotFound',
                404
            );
        }

        return $importacion;
    }

    public function ensureLotAccess(User $user, PqExcelImportacion $importacion): void
    {
        $procedimientoHost = (string) ($importacion->proceso?->procedimiento_host ?? '');
        $this->visibilityPermissionGuard->ensureAltaPermission($user, $procedimientoHost);
    }

    public function ensureHistorialAccess(User $user): void
    {
        $procedimiento = (string) config('excel_import.historialProcedimiento');
        $this->visibilityPermissionGuard->ensureRepoPermission($user, $procedimiento);
    }

    /**
     * @return list<int>
     */
    public function resolveAllowedProcesoIdsForHistorial(User $user): array
    {
        $procesos = PqExcelProceso::query()->where('activo', true)->get();
        $ids = [];

        foreach ($procesos as $proceso) {
            if ($this->visibilityPermissionGuard->hasPermission($user, (string) $proceso->procedimiento_host, 'repo')) {
                $ids[] = (int) $proceso->id;
            }
        }

        return $ids;
    }
}
