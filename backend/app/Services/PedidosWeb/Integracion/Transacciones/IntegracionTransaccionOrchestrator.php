<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones;

use App\Exceptions\IntegracionMaestroAbortException;
use App\Support\IntegracionMaestroErrorCodes;
use Illuminate\Support\Str;

final class IntegracionTransaccionOrchestrator
{
    public function __construct(
        private readonly IntegracionTransaccionRegistry $registry,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function process(string $recurso, array $payload): IntegracionTransaccionProcessResult
    {
        if (! $this->registry->has($recurso)) {
            throw new IntegracionMaestroAbortException(
                IntegracionMaestroErrorCodes::recursoDesconocido,
                'integracion.transaccion.recursoDesconocido',
                404,
            );
        }

        $handler = $this->registry->resolve($recurso);

        if ($handler === null) {
            throw new IntegracionMaestroAbortException(
                IntegracionMaestroErrorCodes::recursoDesconocido,
                'integracion.transaccion.recursoDesconocido',
                404,
            );
        }

        $modo = (string) ($payload['modo'] ?? '');
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];

        if ($modo === 'snapshot' && $items === []) {
            throw new IntegracionMaestroAbortException(
                IntegracionMaestroErrorCodes::snapshotVacio,
                'integracion.transaccion.snapshotVacio',
                422,
            );
        }

        if ($modo === 'snapshot' && ! $handler->supportsSnapshot()) {
            throw new IntegracionMaestroAbortException(
                IntegracionMaestroErrorCodes::modoNoSoportado,
                'integracion.transaccion.modoNoSoportado',
                422,
            );
        }

        $context = new IntegracionTransaccionLoteContext(
            (string) Str::uuid(),
            (string) request()->header((string) config('paqsuite_tenant.headerName', 'X-Paq-Cliente'), ''),
            $recurso,
            $modo,
            (string) ($payload['fechaProceso'] ?? ''),
            isset($payload['origen']) ? (string) $payload['origen'] : null,
        );

        $recibidos = count($items);
        $aplicados = 0;
        $omitidos = 0;
        $rechazados = 0;
        $errores = [];
        $processedKeys = [];
        $seenKeys = [];
        $maxErrores = (int) config('integracion_transacciones.max_errores_respuesta', 50);

        foreach ($items as $indice => $item) {
            if (! is_array($item)) {
                $rechazados++;
                $errores = $this->appendError($errores, (int) $indice, null, 'integracion.transaccion.itemInvalido', $maxErrores);
                continue;
            }

            $itemKey = $handler->keyForItem($item);
            if ($itemKey !== null && isset($seenKeys[$itemKey])) {
                $result = IntegracionTransaccionItemResult::rejected(
                    $itemKey,
                    'integracion.transaccion.duplicado',
                );
            } else {
                $result = $handler->processItem($item, $context);
                if ($itemKey !== null) {
                    $seenKeys[$itemKey] = true;
                }
                $processedKeys[] = $result->clave;
            }

            if ($result->isApplied()) {
                $aplicados++;
            } elseif ($result->isOmitted()) {
                $omitidos++;
            } else {
                $rechazados++;
                $errores = $this->appendError(
                    $errores,
                    (int) $indice,
                    $result->clave,
                    $result->errorCode ?? 'integracion.transaccion.itemInvalido',
                    $maxErrores,
                );
            }
        }

        if ($modo === 'snapshot' && $rechazados === 0) {
            foreach ($handler->finalizeSnapshot($context, $processedKeys) as $result) {
                if ($result->isOmitted()) {
                    $omitidos++;
                } elseif ($result->isRejected()) {
                    $rechazados++;
                    $errores = $this->appendError(
                        $errores,
                        $recibidos,
                        $result->clave,
                        $result->errorCode ?? 'integracion.transaccion.snapshotAbortado',
                        $maxErrores,
                    );
                }
            }
        }

        if ($modo === 'snapshot' && $rechazados > 0) {
            throw new IntegracionMaestroAbortException(
                IntegracionMaestroErrorCodes::jsonInvalido,
                'integracion.transaccion.snapshotAbortado',
                422,
            );
        }

        return new IntegracionTransaccionProcessResult(
            $recurso,
            $modo,
            $recibidos,
            $aplicados,
            $omitidos,
            $rechazados,
            $errores,
            $rechazados > count($errores),
        );
    }

    /**
     * @param  array<int, array{indice: int, clave: string|null, codigo: string}>  $errores
     * @return array<int, array{indice: int, clave: string|null, codigo: string}>
     */
    private function appendError(
        array $errores,
        int $indice,
        ?string $clave,
        string $codigo,
        int $max,
    ): array {
        if (count($errores) < $max) {
            $errores[] = ['indice' => $indice, 'clave' => $clave, 'codigo' => $codigo];
        }

        return $errores;
    }
}
