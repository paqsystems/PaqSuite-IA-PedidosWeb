<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthFlowException;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Auth\ChangePasswordService;
use App\Services\Auth\LoginService;
use App\Services\Auth\SessionContextBuilder;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthController extends Controller
{
    public function __construct(
        private readonly LoginService $loginService,
        private readonly ChangePasswordService $changePasswordService,
        private readonly SessionContextBuilder $sessionContextBuilder,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        try {
            $resultado = $this->loginService->login(
                $validated['codigo'],
                $validated['password']
            );
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $currentToken = $user->currentAccessToken();

            if ($currentToken !== null) {
                $currentToken->delete();
            } else {
                $plainTextToken = $request->bearerToken();
                PersonalAccessToken::findToken($plainTextToken)?->delete();
            }

            Auth::guard('web')->logout();
        }

        return ApiResponse::success([], 'auth.logoutOk');
    }

    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return ApiResponse::error(
                AuthErrorCodes::unauthenticated,
                'auth.unauthenticated',
                401
            );
        }

        try {
            $resultado = $this->sessionContextBuilder->build($user);
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/password/change",
     *     summary="Cambio de contraseña autenticado",
     *     tags={"Auth"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"currentPassword","newPassword","newPasswordConfirmation"},
     *             @OA\Property(property="currentPassword", type="string"),
     *             @OA\Property(property="newPassword", type="string"),
     *             @OA\Property(property="newPasswordConfirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Contraseña actualizada"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Cuenta inhabilitada"),
     *     @OA\Response(response=422, description="Validación o contraseña actual incorrecta")
     * )
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
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
            $resultado = $this->changePasswordService->change(
                $user,
                (string) $request->validated('currentPassword'),
                (string) $request->validated('newPassword'),
            );
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        return ApiResponse::success($resultado, 'auth.passwordChanged');
    }
}
