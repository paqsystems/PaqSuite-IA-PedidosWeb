<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PresupuestoController extends PedidoController
{
    public function store(Request $request): JsonResponse
    {
        return $this->grabarAlias($request, 'presupuesto', null);
    }

    public function update(Request $request, string $codPedido): JsonResponse
    {
        return $this->grabarAlias($request, 'presupuesto', $codPedido);
    }
}
