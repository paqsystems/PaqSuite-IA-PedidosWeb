<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones;

use Illuminate\Contracts\Container\Container;

final class IntegracionTransaccionRegistry
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function has(string $recurso): bool
    {
        return array_key_exists($recurso, config('integracion_transacciones.resources', []));
    }

    public function resolve(string $recurso): ?IntegracionTransaccionHandlerInterface
    {
        $definition = config('integracion_transacciones.resources.'.$recurso);

        if (! is_array($definition) || ! is_string($definition['handler'] ?? null)) {
            return null;
        }

        $handler = $this->container->make($definition['handler'], ['definition' => $definition]);

        return $handler instanceof IntegracionTransaccionHandlerInterface ? $handler : null;
    }

    public function supportsSnapshot(string $recurso): bool
    {
        $definition = config('integracion_transacciones.resources.'.$recurso);

        return is_array($definition) && (bool) ($definition['supports_snapshot'] ?? false);
    }
}
