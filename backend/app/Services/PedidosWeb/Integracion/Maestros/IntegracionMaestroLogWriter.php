<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

use App\Services\PedidosWeb\LogIntegracionService;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionProcessResult;

final class IntegracionMaestroLogWriter
{
    public function __construct(
        private readonly LogIntegracionService $logIntegracionService,
    ) {}

    public function write(IntegracionMaestroProcessResult $result, ?string $origen): void
    {
        $severidad = 'info';

        if ($result->rechazados > 0) {
            $severidad = 'warning';
        }

        $this->logIntegracionService->registrar(
            'maestro_import',
            $severidad,
            $origen ?? 'erp',
            'integracion.maestro.log.procesado',
            [
                'recurso' => $result->recurso,
                'modo' => $result->modo,
                'recibidos' => $result->recibidos,
                'aplicados' => $result->aplicados,
                'omitidos' => $result->omitidos,
                'rechazados' => $result->rechazados,
            ],
            false,
        );
    }

    public function writeAborted(string $recurso, string $respuestaKey, ?string $origen): void
    {
        $this->logIntegracionService->registrar(
            'maestro_import',
            'error',
            $origen ?? 'erp',
            $respuestaKey,
            ['recurso' => $recurso],
            false,
        );
    }

    public function writeTransaction(IntegracionTransaccionProcessResult $result, ?string $origen): void
    {
        $severidad = $result->rechazados > 0 ? 'warning' : 'info';

        $this->logIntegracionService->registrar(
            'transaccion_import',
            $severidad,
            $origen ?? 'erp',
            'integracion.transaccion.log.procesado',
            [
                'recurso' => $result->recurso,
                'modo' => $result->modo,
                'recibidos' => $result->recibidos,
                'aplicados' => $result->aplicados,
                'omitidos' => $result->omitidos,
                'rechazados' => $result->rechazados,
            ],
            false,
        );
    }

    public function writeAbortedTransaction(string $recurso, string $respuestaKey, ?string $origen): void
    {
        $this->logIntegracionService->registrar(
            'transaccion_import',
            'error',
            $origen ?? 'erp',
            $respuestaKey,
            ['recurso' => $recurso],
            false,
        );
    }
}
