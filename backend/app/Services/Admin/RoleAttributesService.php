<?php

namespace App\Services\Admin;

use App\Exceptions\AuthFlowException;
use App\Models\PqMenu;
use App\Models\PqRolAtributo;
use App\Support\AuthErrorCodes;
use Illuminate\Support\Facades\DB;

final class RoleAttributesService
{
    public function __construct(
        private readonly AdminRoleService $adminRoleService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getForRole(int $rolId): array
    {
        $rol = $this->adminRoleService->findRoleOrFail($rolId);
        $readOnly = (bool) $rol->acceso_total;

        if ($readOnly) {
            return [
                'readOnly' => true,
                'rol' => $this->mapRolHeader($rol),
                'items' => [],
            ];
        }

        $existingByProcedimiento = PqRolAtributo::query()
            ->where('id_rol', $rol->id)
            ->get()
            ->keyBy('procedimiento');

        $menuKeyByProcedimiento = collect(config('paqsuite_mvp.menuItems', []))
            ->keyBy('procedimiento');

        $items = $this->eligibleMenus()->map(function (PqMenu $menu) use ($existingByProcedimiento, $menuKeyByProcedimiento): array {
            $procedimiento = (string) $menu->procedimiento;
            /** @var PqRolAtributo|null $existing */
            $existing = $existingByProcedimiento->get($procedimiento);
            $configItem = $menuKeyByProcedimiento->get($procedimiento);

            return [
                'procedimiento' => $procedimiento,
                'menuText' => (string) $menu->text,
                'menuKey' => is_array($configItem) ? (string) $configItem['menuKey'] : $procedimiento,
                'permisoAlta' => (bool) ($existing?->permiso_alta ?? false),
                'permisoBaja' => (bool) ($existing?->permiso_baja ?? false),
                'permisoModi' => (bool) ($existing?->permiso_modi ?? false),
                'permisoRepo' => (bool) ($existing?->permiso_repo ?? false),
            ];
        })->values()->all();

        return [
            'readOnly' => false,
            'rol' => $this->mapRolHeader($rol),
            'items' => $items,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{actualizados: int, eliminados: int}
     */
    public function syncForRole(int $rolId, array $items): array
    {
        $rol = $this->adminRoleService->findRoleOrFail($rolId);

        if ((bool) $rol->acceso_total) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'admin.roles.atributosAccesoTotalReadOnly', 422);
        }

        $eligibleProcedimientos = $this->eligibleMenus()
            ->pluck('procedimiento')
            ->map(static fn ($value): string => (string) $value)
            ->all();

        $eligibleLookup = array_fill_keys($eligibleProcedimientos, true);

        $actualizados = 0;
        $eliminados = 0;

        DB::transaction(function () use ($items, $rol, $eligibleLookup, &$actualizados, &$eliminados): void {
            foreach ($items as $item) {
                $procedimiento = (string) ($item['procedimiento'] ?? '');

                if ($procedimiento === '' || ! isset($eligibleLookup[$procedimiento])) {
                    throw new AuthFlowException(AuthErrorCodes::validationFailed, 'validation.invalid', 422);
                }

                $flags = [
                    'permiso_alta' => (bool) ($item['permisoAlta'] ?? false),
                    'permiso_baja' => (bool) ($item['permisoBaja'] ?? false),
                    'permiso_modi' => (bool) ($item['permisoModi'] ?? false),
                    'permiso_repo' => (bool) ($item['permisoRepo'] ?? false),
                ];

                $hasAny = $flags['permiso_alta'] || $flags['permiso_baja'] || $flags['permiso_modi'] || $flags['permiso_repo'];

                if (! $hasAny) {
                    $deleted = PqRolAtributo::query()
                        ->where('id_rol', $rol->id)
                        ->where('procedimiento', $procedimiento)
                        ->delete();

                    if ($deleted > 0) {
                        $eliminados++;
                    }

                    continue;
                }

                PqRolAtributo::query()->updateOrInsert(
                    [
                        'id_rol' => $rol->id,
                        'procedimiento' => $procedimiento,
                    ],
                    array_merge($flags, [
                        'id_rol' => $rol->id,
                        'procedimiento' => $procedimiento,
                    ])
                );

                $actualizados++;
            }
        });

        return [
            'actualizados' => $actualizados,
            'eliminados' => $eliminados,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, PqMenu>
     */
    private function eligibleMenus()
    {
        return PqMenu::query()
            ->where('enabled', true)
            ->whereNotNull('procedimiento')
            ->where('procedimiento', '!=', '')
            ->where('procedimiento', 'not like', 'grp_%')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRolHeader(\App\Models\PqRol $rol): array
    {
        return [
            'id' => (int) $rol->id,
            'nombreRol' => (string) $rol->nombre_rol,
            'accesoTotal' => (bool) $rol->acceso_total,
        ];
    }
}
