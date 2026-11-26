<?php

namespace App\Services\PedidosWeb\Integracion\Export;

use App\Http\Requests\PedidosWeb\IntegracionExportComprobantesRequest;
use App\Services\PedidosWeb\LogIntegracionService;
use Illuminate\Support\Facades\Log;

final class IntegracionExportLogWriter
{
    public function __construct(
        private readonly LogIntegracionService $logIntegracionService,
    ) {}

    /**
     * @param  array<string, mixed>  $resultado
     */
    public function logSuccess(IntegracionExportComprobantesRequest $request, array $resultado): void
    {
        try {
            $this->logIntegracionService->registrar(
                'export_comprobantes',
                'info',
                (string) $request->user()?->codigo ?: 'integracion',
                'integracion.export.ok',
                [
                    'page' => $resultado['page'] ?? null,
                    'pageSize' => $resultado['pageSize'] ?? null,
                    'total' => $resultado['total'] ?? null,
                    'estadoRangos' => $request->input('estadoRangosTokens'),
                    'codCliente' => $request->input('codCliente'),
                    'codPedido' => $request->input('codPedido'),
                ],
                true,
            );
        } catch (\Throwable $throwable) {
            Log::warning('integracion.export.log_failed', [
                'message' => $throwable->getMessage(),
            ]);
        }
    }
}