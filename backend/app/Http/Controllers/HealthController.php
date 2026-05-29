<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'error' => false,
            'respuesta' => 'ok',
            'resultado' => [
                'serviceName' => 'PaqSuite-IA-PedidosWeb',
                'status' => 'up',
            ],
        ]);
    }
}
