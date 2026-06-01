<?php

namespace App\Services\GridLayout;

use App\Models\PqGridLayout;
use App\Models\PqGridLayoutLastUsed;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class GridLayoutService
{
    private const MAX_STATE_JSON_BYTES = 524288;

    /**
     * @return list<array<string, mixed>>
     */
    public function listLayouts(string $proceso, string $gridId, User $user): array
    {
        return PqGridLayout::query()
            ->where('proceso', $proceso)
            ->where('grid_id', $gridId)
            ->orderBy('layout_name')
            ->get()
            ->map(fn (PqGridLayout $layout): array => $this->toListItem($layout, $user))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getActiveLayout(string $proceso, string $gridId, User $user): array
    {
        $lastUsed = PqGridLayoutLastUsed::query()
            ->where('user_id', $user->id)
            ->where('proceso', $proceso)
            ->where('grid_id', $gridId)
            ->first();

        if ($lastUsed === null || $lastUsed->layout_id === null) {
            return [
                'layoutId' => null,
                'layoutName' => null,
                'stateJson' => null,
            ];
        }

        $layout = PqGridLayout::query()->find($lastUsed->layout_id);

        if ($layout === null) {
            return [
                'layoutId' => null,
                'layoutName' => null,
                'stateJson' => null,
            ];
        }

        return $this->toActivePayload($layout);
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $stateJson
     * @return array<string, mixed>
     */
    public function createLayout(
        string $proceso,
        string $gridId,
        string $layoutName,
        array $stateJson,
        User $user
    ): array {
        $encodedState = $this->encodeStateJson($stateJson);

        $duplicate = PqGridLayout::query()
            ->where('proceso', $proceso)
            ->where('grid_id', $gridId)
            ->where('layout_name', $layoutName)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'layoutName' => ['gridLayout.duplicateName'],
            ]);
        }

        $layout = PqGridLayout::query()->create([
            'proceso' => $proceso,
            'grid_id' => $gridId,
            'layout_name' => $layoutName,
            'created_by_user_id' => $user->id,
            'state_json' => $encodedState,
        ]);

        $this->setLastUsed($user, $proceso, $gridId, (int) $layout->id);

        return $this->toActivePayload($layout);
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $stateJson
     * @return array<string, mixed>
     */
    public function updateLayout(PqGridLayout $layout, array $stateJson, User $user): array
    {
        $layout->state_json = $this->encodeStateJson($stateJson);
        $layout->save();

        $this->setLastUsed($user, $layout->proceso, $layout->grid_id, (int) $layout->id);

        return $this->toActivePayload($layout->fresh());
    }

    public function deleteLayout(PqGridLayout $layout, User $user): void
    {
        DB::transaction(function () use ($layout, $user): void {
            PqGridLayoutLastUsed::query()
                ->where('user_id', $user->id)
                ->where('proceso', $layout->proceso)
                ->where('grid_id', $layout->grid_id)
                ->where('layout_id', $layout->id)
                ->update([
                    'layout_id' => null,
                    'updated_at' => Carbon::now(),
                ]);

            $layout->delete();
        });
    }

    public function setActiveLayout(
        string $proceso,
        string $gridId,
        ?int $layoutId,
        User $user
    ): void {
        if ($layoutId !== null) {
            $exists = PqGridLayout::query()
                ->where('id', $layoutId)
                ->where('proceso', $proceso)
                ->where('grid_id', $gridId)
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'layoutId' => ['validation.failed'],
                ]);
            }
        }

        $this->setLastUsed($user, $proceso, $gridId, $layoutId);
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $stateJson
     */
    private function encodeStateJson(array $stateJson): string
    {
        $encoded = json_encode($stateJson, JSON_THROW_ON_ERROR);

        if (strlen($encoded) > self::MAX_STATE_JSON_BYTES) {
            throw ValidationException::withMessages([
                'stateJson' => ['validation.failed'],
            ]);
        }

        return $encoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function toListItem(PqGridLayout $layout, User $user): array
    {
        return [
            'id' => (int) $layout->id,
            'layoutName' => $layout->layout_name,
            'createdByUserId' => (int) $layout->created_by_user_id,
            'isOwner' => (int) $layout->created_by_user_id === (int) $user->id,
            'updatedAt' => $layout->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toActivePayload(PqGridLayout $layout): array
    {
        return [
            'layoutId' => (int) $layout->id,
            'layoutName' => $layout->layout_name,
            'stateJson' => json_decode($layout->state_json, true, 512, JSON_THROW_ON_ERROR),
        ];
    }

    private function setLastUsed(User $user, string $proceso, string $gridId, ?int $layoutId): void
    {
        PqGridLayoutLastUsed::query()->updateOrInsert(
            [
                'user_id' => $user->id,
                'proceso' => $proceso,
                'grid_id' => $gridId,
            ],
            [
                'layout_id' => $layoutId,
                'updated_at' => Carbon::now(),
            ]
        );
    }

}
