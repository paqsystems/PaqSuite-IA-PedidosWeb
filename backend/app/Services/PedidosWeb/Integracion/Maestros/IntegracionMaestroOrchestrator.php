<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

use App\Exceptions\IntegracionMaestroAbortException;
use App\Support\IntegracionMaestroErrorCodes;
use Illuminate\Support\Str;

final class IntegracionMaestroOrchestrator
{
    public function __construct(
        private readonly IntegracionMaestroRegistry $registry,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function process(string $recurso, array $payload): IntegracionMaestroProcessResult
    {
        if (! $this->registry->has($recurso)) {
            throw new IntegracionMaestroAbortException(
                IntegracionMaestroErrorCodes::recursoDesconocido,
                'integracion.maestro.recursoDesconocido',
                404,
            );
        }

        $handler = $this->registry->resolve($recurso);

        if ($handler === null) {
            throw new IntegracionMaestroAbortException(
                IntegracionMaestroErrorCodes::recursoDesconocido,
                'integracion.maestro.recursoDesconocido',
                404,
            );
        }

        $modo = (string) ($payload['modo'] ?? '');
        $fechaProceso = (string) ($payload['fechaProceso'] ?? '');
        $origen = isset($payload['origen']) ? (string) $payload['origen'] : null;
        /** @var array<int, array<string, mixed>> $items */
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];

        if ($modo === 'snapshot' && ! $handler->supportsSnapshot()) {
            throw new IntegracionMaestroAbortException(
                IntegracionMaestroErrorCodes::modoNoSoportado,
                'integracion.maestro.modoNoSoportado',
                422,
            );
        }

        $batchId = (string) Str::uuid();
        $tenant = (string) request()->header(
            (string) config('paqsuite_tenant.headerName', 'X-Paq-Cliente'),
            '',
        );
        $context = new IntegracionMaestroLoteContext(
            $batchId,
            $tenant,
            $recurso,
            $modo,
            $fechaProceso,
            $origen,
        );

        $recibidos = count($items);
        $aplicados = 0;
        $omitidos = 0;
        $rechazados = 0;
        /** @var array<int, array{indice: int, clave: string|null, codigo: string}> $errores */
        $errores = [];
        $maxErrores = (int) config('integracion_maestros.max_errores_respuesta', 50);
        $processedKeys = [];

        foreach ($items as $indice => $item) {
            if (! is_array($item)) {
                $rechazados++;
                if (count($errores) < $maxErrores) {
                    $errores[] = [
                        'indice' => (int) $indice,
                        'clave' => null,
                        'codigo' => 'integracion.maestro.itemInvalido',
                    ];
                }

                continue;
            }

            $result = $handler->processItem($item, $context);
            $processedKeys[] = $result->clave;

            if ($result->isApplied()) {
                $aplicados++;
            } elseif ($result->isOmitted()) {
                $omitidos++;
            } else {
                $rechazados++;
                if (count($errores) < $maxErrores && $result->errorCode !== null) {
                    $errores[] = [
                        'indice' => (int) $indice,
                        'clave' => $result->clave,
                        'codigo' => $result->errorCode,
                    ];
                }
            }

            if ($recurso !== 'clientes') {
                continue;
            }

            foreach (['direcciones' => 'clientes-direcciones', 'contactos' => 'clientes-contactos'] as $childrenKey => $childResource) {
                $childHandler = $this->registry->resolve($childResource);
                $children = is_array($item[$childrenKey] ?? null) ? $item[$childrenKey] : [];
                if ($childHandler === null || $children === []) {
                    continue;
                }

                foreach ($children as $childIndex => $child) {
                    $recibidos++;
                    if (! is_array($child)) {
                        $rechazados++;
                        if (count($errores) < $maxErrores) {
                            $errores[] = [
                                'indice' => (string) $indice.'.'.(string) $childIndex,
                                'clave' => null,
                                'codigo' => 'integracion.maestro.itemInvalido',
                            ];
                        }
                        continue;
                    }

                    $child['codClient'] ??= $item['codClient'] ?? $item['cod_client'] ?? null;
                    $childResult = $childHandler->processItem($child, $context);
                    if ($childResult->isApplied()) {
                        $aplicados++;
                    } elseif ($childResult->isOmitted()) {
                        $omitidos++;
                    } else {
                        $rechazados++;
                        if (count($errores) < $maxErrores && $childResult->errorCode !== null) {
                            $errores[] = [
                                'indice' => (string) $indice.'.'.(string) $childIndex,
                                'clave' => $childResult->clave,
                                'codigo' => $childResult->errorCode,
                            ];
                        }
                    }
                }
            }
        }

        if ($modo === 'snapshot' && $handler->supportsSnapshot()) {
            foreach ($handler->finalizeSnapshot($context, $processedKeys) as $result) {
                if ($result->isOmitted()) {
                    $omitidos++;
                    continue;
                }

                if ($result->isRejected()) {
                    $rechazados++;
                    if (count($errores) < $maxErrores && $result->errorCode !== null) {
                        $errores[] = [
                            'indice' => $recibidos,
                            'clave' => $result->clave,
                            'codigo' => $result->errorCode,
                        ];
                    }
                }
            }
        }

        $totalErrores = $rechazados;
        $erroresTruncados = $totalErrores > count($errores) && count($errores) >= $maxErrores;

        return new IntegracionMaestroProcessResult(
            $recurso,
            $modo,
            $recibidos,
            $aplicados,
            $omitidos,
            $rechazados,
            $errores,
            $erroresTruncados,
        );
    }
}
