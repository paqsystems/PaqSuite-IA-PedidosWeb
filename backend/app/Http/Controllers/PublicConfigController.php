<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PublicConfigController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'gridLayoutsEnabled' => (bool) config('paqsuite_mvp.gridLayoutsEnabled'),
        ]);
    }
}
