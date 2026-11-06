<?php

namespace App\Services\PedidosWeb\Integracion\Maestros\Handlers;

final class ClientesRelacionMaestroHandler extends IntegracionMaestroJsonHandler
{
    protected function normalize(array $item): array
    {
        if (($this->definition['resource'] ?? null) === 'clientes-direcciones'
            && array_key_exists('habitual', $item)) {
            $value = $item['habitual'];
            $truthy = is_bool($value)
                ? $value
                : in_array(strtoupper(trim((string) $value)), ['1', 'S', 'Y', 'T', 'TRUE'], true);
            $item['habitual'] = $truthy ? 'S' : 'N';
        }

        return $item;
    }
}
