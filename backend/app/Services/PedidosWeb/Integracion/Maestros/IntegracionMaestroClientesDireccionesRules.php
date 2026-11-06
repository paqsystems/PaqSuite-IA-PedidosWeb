<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

use App\Models\PqPedidoswebClienteDireccionEntrega;

final class IntegracionMaestroClientesDireccionesRules
{
    /**
     * @param  array<int, mixed>  $direcciones
     */
    public static function habitualCount(array $direcciones): int
    {
        $count = 0;
        foreach ($direcciones as $direccion) {
            if (! is_array($direccion)) {
                continue;
            }
            if (! array_key_exists('habitual', $direccion)) {
                continue;
            }
            if (PqPedidoswebClienteDireccionEntrega::isHabitualFlag($direccion['habitual'])) {
                $count++;
            }
        }

        return $count;
    }
}
