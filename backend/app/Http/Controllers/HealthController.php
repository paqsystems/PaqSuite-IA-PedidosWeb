<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return ApiResponse::success([
            'serviceName' => config('app.name'),
            'status' => 'up',
        ]);
    }
}
