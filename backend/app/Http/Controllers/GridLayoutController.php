<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\PqGridLayout;
use App\Services\GridLayout\GridLayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class GridLayoutController extends Controller
{
    public function __construct(
        private readonly GridLayoutService $gridLayoutService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'proceso' => ['required', 'string', 'max:128'],
            'gridId' => ['required', 'string', 'max:64'],
        ]);

        $user = $request->user();
        assert($user !== null);

        $items = $this->gridLayoutService->listLayouts(
            $validated['proceso'],
            $validated['gridId'],
            $user
        );

        return ApiResponse::success(['items' => $items]);
    }

    public function active(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'proceso' => ['required', 'string', 'max:128'],
            'gridId' => ['required', 'string', 'max:64'],
        ]);

        $user = $request->user();
        assert($user !== null);

        $payload = $this->gridLayoutService->getActiveLayout(
            $validated['proceso'],
            $validated['gridId'],
            $user
        );

        return ApiResponse::success($payload);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'proceso' => ['required', 'string', 'max:128'],
            'gridId' => ['required', 'string', 'max:64'],
            'layoutName' => ['required', 'string', 'max:128'],
            'stateJson' => ['required', 'array'],
        ]);

        $user = $request->user();
        assert($user !== null);

        try {
            $created = $this->gridLayoutService->createLayout(
                $validated['proceso'],
                $validated['gridId'],
                $validated['layoutName'],
                $validated['stateJson'],
                $user
            );
        } catch (ValidationException $exception) {
            $layoutNameErrors = $exception->errors()['layoutName'] ?? [];

            if (in_array('gridLayout.duplicateName', $layoutNameErrors, true)) {
                return ApiResponse::error(2001, 'gridLayout.duplicateName', 409);
            }

            throw $exception;
        }

        return ApiResponse::success($created, 'gridLayout.created', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'stateJson' => ['required', 'array'],
        ]);

        $user = $request->user();
        assert($user !== null);

        $layout = PqGridLayout::query()->findOrFail($id);

        if ((int) $layout->created_by_user_id !== (int) $user->id) {
            return ApiResponse::error(3001, 'gridLayout.forbidden', 403);
        }

        $updated = $this->gridLayoutService->updateLayout(
            $layout,
            $validated['stateJson'],
            $user
        );

        return ApiResponse::success($updated, 'gridLayout.updated');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        assert($user !== null);

        $layout = PqGridLayout::query()->findOrFail($id);

        if ((int) $layout->created_by_user_id !== (int) $user->id) {
            return ApiResponse::error(3001, 'gridLayout.forbidden', 403);
        }

        $this->gridLayoutService->deleteLayout($layout, $user);

        return ApiResponse::success([], 'gridLayout.deleted');
    }

    public function setActive(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'proceso' => ['required', 'string', 'max:128'],
            'gridId' => ['required', 'string', 'max:64'],
            'layoutId' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        assert($user !== null);

        $this->gridLayoutService->setActiveLayout(
            $validated['proceso'],
            $validated['gridId'],
            $validated['layoutId'] ?? null,
            $user
        );

        return ApiResponse::success([], 'gridLayout.activeUpdated');
    }
}
