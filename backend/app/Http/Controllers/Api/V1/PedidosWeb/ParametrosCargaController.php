<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\ParametrosCargaService;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ParametrosCargaController extends Controller
{
    public function __construct(
        private readonly ParametrosCargaService $parametrosCargaService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        return ApiResponse::success($this->parametrosCargaService->forUser($user));
    }
}
