<?php

namespace App\Http\Middleware;

use App\Exceptions\AuthFlowException;
use App\Http\Responses\ApiResponse;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIntegracionImportacion
{
    public function __construct(
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                AuthErrorCodes::unauthenticated,
                'auth.unauthenticated',
                401,
            );
        }

        $procedimiento = (string) config('paqsuite_visibility.procedimientos.integracionMaestros');

        try {
            $this->visibilityPermissionGuard->ensureAltaPermission($user, $procedimiento);
        } catch (AuthFlowException) {
            $path = $request->path();
            $claveRespuesta = 'integracion.maestro.forbidden';
            if (str_contains($path, '/integracion/transacciones/')) {
                $claveRespuesta = 'integracion.transaccion.forbidden';
            } elseif (str_contains($path, '/integracion/comprobantes')) {
                $claveRespuesta = 'integracion.export.forbidden';
            }

            return ApiResponse::error(
                AuthErrorCodes::noPermission,
                $claveRespuesta,
                403,
            );
        }

        return $next($request);
    }
}
