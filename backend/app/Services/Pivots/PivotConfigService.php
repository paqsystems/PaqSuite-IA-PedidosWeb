<?php

namespace App\Services\Pivots;

use App\Models\PqPivotConfig;
use App\Models\PqPivotConfigLastUsed;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PivotConfigService
{
    private const maxConfigJsonBytes = 524288;

    /**
     * @return list<array<string, mixed>>
     */
    public function listConfigs(string $consultaId, User $user): array
    {
        return PqPivotConfig::query()
            ->where('consulta_id', $consultaId)
            ->where('eliminado', false)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn (PqPivotConfig $config): array => $this->toListItem($config, $user))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getActiveConfig(string $consultaId, User $user): array
    {
        $lastUsed = PqPivotConfigLastUsed::query()
            ->where('user_id', $user->id)
            ->where('consulta_id', $consultaId)
            ->first();

        if ($lastUsed === null) {
            return [
                'configId' => null,
                'nombre' => null,
                'configuracionJson' => null,
                'restoreMode' => 'pivotBase',
            ];
        }

        if ($lastUsed->pivot_id === null) {
            return [
                'configId' => null,
                'nombre' => null,
                'configuracionJson' => null,
                'restoreMode' => 'empty',
            ];
        }

        $config = PqPivotConfig::query()
            ->where('pivot_id', $lastUsed->pivot_id)
            ->where('consulta_id', $consultaId)
            ->where('eliminado', false)
            ->first();

        if ($config === null) {
            return [
                'configId' => null,
                'nombre' => null,
                'configuracionJson' => null,
                'restoreMode' => 'pivotBase',
            ];
        }

        return $this->toActivePayload($config);
    }

    /**
     * @param  array<string, mixed>  $configuracionJson
     * @return array<string, mixed>
     */
    public function createConfig(
        string $consultaId,
        string $nombre,
        array $configuracionJson,
        int $versionDefinicionConsulta,
        User $user
    ): array {
        $encoded = $this->encodeConfiguracionJson($configuracionJson);

        $duplicate = PqPivotConfig::query()
            ->where('consulta_id', $consultaId)
            ->where('nombre', $nombre)
            ->where('eliminado', false)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'nombre' => ['pivotLayout.duplicateName'],
            ]);
        }

        $config = PqPivotConfig::query()->create([
            'consulta_id' => $consultaId,
            'nombre' => $nombre,
            'configuracion_json' => $encoded,
            'version_definicion_consulta' => $versionDefinicionConsulta,
            'created_by_user_id' => $user->id,
            'eliminado' => false,
            'activo' => true,
        ]);

        $this->setLastUsed($user, $consultaId, (int) $config->pivot_id);

        return $this->toActivePayload($config);
    }

    /**
     * @param  array<string, mixed>  $configuracionJson
     * @return array<string, mixed>
     */
    public function updateConfig(PqPivotConfig $config, array $configuracionJson, User $user): array
    {
        $config->configuracion_json = $this->encodeConfiguracionJson($configuracionJson);
        $config->save();

        $this->setLastUsed($user, $config->consulta_id, (int) $config->pivot_id);

        return $this->toActivePayload($config->fresh());
    }

    public function deleteConfig(PqPivotConfig $config, User $user): void
    {
        DB::transaction(function () use ($config, $user): void {
            PqPivotConfigLastUsed::query()
                ->where('user_id', $user->id)
                ->where('consulta_id', $config->consulta_id)
                ->where('pivot_id', $config->pivot_id)
                ->update([
                    'pivot_id' => null,
                    'updated_at' => Carbon::now(),
                ]);

            $config->eliminado = true;
            $config->activo = false;
            $config->save();
        });
    }

    public function setActiveConfig(string $consultaId, ?int $configId, User $user): void
    {
        if ($configId !== null) {
            $exists = PqPivotConfig::query()
                ->where('pivot_id', $configId)
                ->where('consulta_id', $consultaId)
                ->where('eliminado', false)
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'configId' => ['validation.failed'],
                ]);
            }
        }

        $this->setLastUsed($user, $consultaId, $configId);
    }

    /**
     * @param  array<string, mixed>  $configuracionJson
     */
    private function encodeConfiguracionJson(array $configuracionJson): string
    {
        $encoded = json_encode($configuracionJson, JSON_THROW_ON_ERROR);

        if (strlen($encoded) > self::maxConfigJsonBytes) {
            throw ValidationException::withMessages([
                'configuracionJson' => ['pivotLayout.payloadTooLarge'],
            ]);
        }

        return $encoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function toListItem(PqPivotConfig $config, User $user): array
    {
        return [
            'configId' => (int) $config->pivot_id,
            'nombre' => $config->nombre,
            'createdByUserId' => (int) $config->created_by_user_id,
            'isOwner' => (int) $config->created_by_user_id === (int) $user->id,
            'updatedAt' => $config->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toActivePayload(PqPivotConfig $config): array
    {
        return [
            'configId' => (int) $config->pivot_id,
            'nombre' => $config->nombre,
            'configuracionJson' => json_decode($config->configuracion_json, true, 512, JSON_THROW_ON_ERROR),
            'versionDefinicionConsulta' => (int) $config->version_definicion_consulta,
            'restoreMode' => 'saved',
        ];
    }

    private function setLastUsed(User $user, string $consultaId, ?int $configId): void
    {
        PqPivotConfigLastUsed::query()->updateOrInsert(
            [
                'user_id' => $user->id,
                'consulta_id' => $consultaId,
            ],
            [
                'pivot_id' => $configId,
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
