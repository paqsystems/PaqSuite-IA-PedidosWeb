<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Support\AuthErrorCodes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminSecurityEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('paqsuite_mvp.securityAdminEnabled')) {
            return ApiResponse::error(AuthErrorCodes::notFound, 'admin.security.notEnabled', 404);
        }

        return $next($request);
    }
}
