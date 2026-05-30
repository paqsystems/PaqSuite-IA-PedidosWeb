<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/health",
     *     summary="Health check del servicio",
     *     tags={"System"},
     *     @OA\Response(response=200, description="Servicio operativo", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeHealth")),
     * )
     */
    public function __invoke(): JsonResponse
    {
        return ApiResponse::success([
            'serviceName' => config('app.name'),
            'status' => 'up',
        ]);
    }
}
