<?php

namespace App\Services\Admin;

use App\Exceptions\AuthFlowException;
use App\Models\PqPermiso;
use App\Models\User;
use App\Support\AuthErrorCodes;
use Illuminate\Support\Facades\DB;

final class PermisoBatchService
{
    public function __construct(
        private readonly AdminRoleService $adminRoleService,
    ) {}

    /**
     * @param  array{mode: string, anchorId: int, rolIds?: array<int, int>, usuarioIds?: array<int, int>}  $payload
     * @return array{creados: int, omitidos: int}
     */
    public function createBatch(array $payload): array
    {
        $mode = (string) ($payload['mode'] ?? '');
        $anchorId = (int) ($payload['anchorId'] ?? 0);
        $maxIds = (int) config('paqsuite_admin_security.batchMaxSecondaryIds', 500);

        if (! in_array($mode, ['by_user', 'by_role'], true)) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'validation.invalid', 422);
        }

        if ($anchorId <= 0) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'admin.permisos.bulk.validationNoAnchor', 422);
        }

        $secondaryIds = $mode === 'by_user'
            ? array_values(array_unique(array_map('intval', (array) ($payload['rolIds'] ?? []))))
            : array_values(array_unique(array_map('intval', (array) ($payload['usuarioIds'] ?? []))));

        if ($secondaryIds === []) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'admin.permisos.bulk.validationSinCombinaciones', 422);
        }

        if (count($secondaryIds) > $maxIds) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'admin.permisos.bulk.tooMany', 422);
        }

        if ($mode === 'by_user') {
            $this->assertActiveUser($anchorId);

            foreach ($secondaryIds as $rolId) {
                $this->adminRoleService->findRoleOrFail($rolId);
            }

            return $this->persistPairs(
                static fn (int $rolId): array => ['id_usuario' => $anchorId, 'id_rol' => $rolId],
                $secondaryIds
            );
        }

        $this->adminRoleService->findRoleOrFail($anchorId);

        foreach ($secondaryIds as $usuarioId) {
            $this->assertActiveUser($usuarioId);
        }

        return $this->persistPairs(
            static fn (int $usuarioId): array => ['id_usuario' => $usuarioId, 'id_rol' => $anchorId],
            $secondaryIds
        );
    }

    /**
     * @param  callable(int): array{id_usuario: int, id_rol: int}  $pairFactory
     * @param  array<int, int>  $secondaryIds
     * @return array{creados: int, omitidos: int}
     */
    private function persistPairs(callable $pairFactory, array $secondaryIds): array
    {
        $creados = 0;
        $omitidos = 0;
        $monoEmpresaId = (int) config('paqsuite_seed.monoEmpresaId');

        DB::transaction(function () use ($pairFactory, $secondaryIds, $monoEmpresaId, &$creados, &$omitidos): void {
            foreach ($secondaryIds as $secondaryId) {
                $pair = $pairFactory($secondaryId);

                $exists = PqPermiso::query()
                    ->where('id_usuario', $pair['id_usuario'])
                    ->where('id_rol', $pair['id_rol'])
                    ->where('id_empresa', $monoEmpresaId)
                    ->exists();

                if ($exists) {
                    $omitidos++;
                    continue;
                }

                PqPermiso::query()->create([
                    'id_usuario' => $pair['id_usuario'],
                    'id_rol' => $pair['id_rol'],
                    'id_empresa' => $monoEmpresaId,
                ]);

                $creados++;
            }
        });

        return [
            'creados' => $creados,
            'omitidos' => $omitidos,
        ];
    }

    private function assertActiveUser(int $userId): void
    {
        $exists = User::query()
            ->where('id', $userId)
            ->where('activo', true)
            ->where('inhabilitado', false)
            ->exists();

        if (! $exists) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'validation.invalid', 422);
        }
    }
}
