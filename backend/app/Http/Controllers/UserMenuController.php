<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthFlowException;
use App\Http\Responses\ApiResponse;
use App\Services\Menu\AuthorizedMenuBuilder;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserMenuController extends Controller
{
    public function __construct(
        private readonly AuthorizedMenuBuilder $authorizedMenuBuilder,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/user/menu",
     *     summary="Menu autorizado del usuario",
     *     tags={"Menu"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Arbol de menu", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeMenuList")),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso de menu")
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                AuthErrorCodes::unauthenticated,
                'auth.unauthenticated',
                401
            );
        }

        try {
            $items = $this->authorizedMenuBuilder->buildForUser($user);
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($items);
    }
}
