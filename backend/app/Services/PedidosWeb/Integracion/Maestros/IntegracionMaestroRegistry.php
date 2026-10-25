<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

use Illuminate\Contracts\Container\Container;

final class IntegracionMaestroRegistry
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function has(string $recurso): bool
    {
        $definition = config('integracion_maestros.resources.'.$recurso);

        return is_array($definition) && ! (bool) ($definition['nested_only'] ?? false);
    }

    public function resolve(string $recurso): ?IntegracionMaestroHandlerInterface
    {
        $definition = config('integracion_maestros.resources.'.$recurso);

        if (! is_array($definition)) {
            return null;
        }

        $handlerClass = $definition['handler'] ?? null;

        if (! is_string($handlerClass) || $handlerClass === '') {
            return null;
        }

        $handler = $this->container->make($handlerClass, ['definition' => $definition]);

        if (! $handler instanceof IntegracionMaestroHandlerInterface) {
            return null;
        }

        return $handler;
    }

    public function supportsSnapshot(string $recurso): bool
    {
        $definition = config('integracion_maestros.resources.'.$recurso);

        if (! is_array($definition)) {
            return false;
        }

        if (array_key_exists('supports_snapshot', $definition)) {
            return (bool) $definition['supports_snapshot'];
        }

        $handler = $this->resolve($recurso);

        return $handler?->supportsSnapshot() ?? false;
    }
}
