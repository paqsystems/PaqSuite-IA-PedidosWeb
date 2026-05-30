<?php

namespace App\Services\Menu;

use App\Exceptions\AuthFlowException;
use App\Models\PqMenu;
use App\Models\PqPermiso;
use App\Models\PqRolAtributo;
use App\Models\User;
use App\Support\AuthErrorCodes;
use Illuminate\Support\Collection;

final class AuthorizedMenuBuilder
{
    public function __construct(
        private readonly MenuNodeTypeResolver $menuNodeTypeResolver,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildForUser(User $user): array
    {
        $permiso = PqPermiso::query()
            ->with('rol')
            ->where('id_usuario', $user->id)
            ->where('id_empresa', (int) config('paqsuite_seed.monoEmpresaId'))
            ->first();

        if ($permiso === null || $permiso->rol === null) {
            throw new AuthFlowException(
                AuthErrorCodes::noPermission,
                'auth.noPermission',
                403
            );
        }

        $rol = $permiso->rol;
        $enabledMenus = PqMenu::query()
            ->where('enabled', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $allowedProcedimientos = $this->resolveAllowedProcedimientos($rol->id, (bool) $rol->acceso_total);

        $authorizedMenus = $enabledMenus->filter(
            static fn (PqMenu $menu): bool => $rol->acceso_total
                || $allowedProcedimientos->contains($menu->procedimiento)
        );

        return $this->buildTree($authorizedMenus);
    }

    /**
     * @return Collection<int, string>
     */
    private function resolveAllowedProcedimientos(int $rolId, bool $accesoTotal): Collection
    {
        if ($accesoTotal) {
            return collect();
        }

        return PqRolAtributo::query()
            ->where('id_rol', $rolId)
            ->where('permiso_repo', true)
            ->pluck('procedimiento');
    }

    /**
     * @param  Collection<int, PqMenu>  $menus
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(Collection $menus): array
    {
        if ($menus->isEmpty()) {
            return [];
        }

        $menuKeyByProcedimiento = collect(config('paqsuite_mvp.menuItems', []))
            ->keyBy('procedimiento');

        /** @var array<int, array<string, mixed>> $nodesById */
        $nodesById = [];

        foreach ($menus as $menu) {
            $nodesById[(int) $menu->id] = $this->mapMenuNode($menu, $menuKeyByProcedimiento);
            $nodesById[(int) $menu->id]['children'] = [];
        }

        $roots = [];

        foreach ($menus as $menu) {
            $node = &$nodesById[(int) $menu->id];
            $parentId = (int) $menu->idparent;

            if ($parentId === 0 || ! isset($nodesById[$parentId])) {
                $roots[] = &$node;
                continue;
            }

            $nodesById[$parentId]['children'][] = &$node;
        }

        unset($node);

        usort($roots, static fn (array $left, array $right): int => $left['order'] <=> $right['order']);

        foreach ($roots as &$root) {
            $this->sortChildrenRecursively($root);
        }

        return array_values($roots);
    }

    /**
     * @param  Collection<string, array<string, mixed>>  $menuKeyByProcedimiento
     * @return array<string, mixed>
     */
    private function mapMenuNode(PqMenu $menu, Collection $menuKeyByProcedimiento): array
    {
        $configItem = $menuKeyByProcedimiento->get($menu->procedimiento);
        $menuKey = is_array($configItem) ? (string) $configItem['menuKey'] : (string) $menu->procedimiento;
        $routePath = (string) ($menu->routeName ?? '');
        $tipoProceso = (string) ($menu->tipo_proceso ?? '');

        return [
            'id' => (int) $menu->id,
            'menuKey' => $menuKey,
            'labelKey' => 'menu.'.$menuKey,
            'text' => (string) $menu->text,
            'routePath' => $routePath !== '' ? $routePath : null,
            'procedimiento' => (string) $menu->procedimiento,
            'tipoProceso' => $tipoProceso !== '' ? $tipoProceso : null,
            'order' => (int) $menu->orden,
            'nodeType' => $this->menuNodeTypeResolver->resolve($routePath, $tipoProceso),
            'children' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function sortChildrenRecursively(array &$node): void
    {
        if (! is_array($node['children']) || $node['children'] === []) {
            return;
        }

        usort(
            $node['children'],
            static fn (array $left, array $right): int => $left['order'] <=> $right['order']
        );

        foreach ($node['children'] as &$child) {
            $this->sortChildrenRecursively($child);
        }
    }
}
