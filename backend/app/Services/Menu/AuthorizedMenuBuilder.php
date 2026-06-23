<?php

namespace App\Services\Menu;

use App\Models\PqMenu;
use App\Models\User;
use App\Services\Security\UserRoleUnionService;
use Illuminate\Support\Collection;

final class AuthorizedMenuBuilder
{
    public function __construct(
        private readonly MenuNodeTypeResolver $menuNodeTypeResolver,
        private readonly UserRoleUnionService $userRoleUnionService,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildForUser(User $user): array
    {
        $union = $this->userRoleUnionService->resolveForUser($user);

        $enabledMenus = PqMenu::query()
            ->where('enabled', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        if ($union->hasAccesoTotal()) {
            $authorizedMenus = $enabledMenus;
        } else {
            $allowedProcedimientos = $union->getRepoProcedimientos();

            $authorizedMenus = $enabledMenus->filter(
                static fn (PqMenu $menu): bool => $allowedProcedimientos->contains($menu->procedimiento)
            );

            $authorizedMenus = $this->includeMenuAncestors($authorizedMenus, $enabledMenus);
        }

        return $this->buildTree($authorizedMenus);
    }

    /**
     * Incluye nodos padre (p. ej. grp_*) cuando al menos un hijo está autorizado.
     *
     * @param  Collection<int, PqMenu>  $authorizedMenus
     * @param  Collection<int, PqMenu>  $enabledMenus
     * @return Collection<int, PqMenu>
     */
    private function includeMenuAncestors(Collection $authorizedMenus, Collection $enabledMenus): Collection
    {
        if ($authorizedMenus->isEmpty()) {
            return $authorizedMenus;
        }

        $enabledById = $enabledMenus->keyBy(static fn (PqMenu $menu): int => (int) $menu->id);
        $includedIds = [];

        foreach ($authorizedMenus as $menu) {
            $includedIds[(int) $menu->id] = true;
        }

        foreach ($authorizedMenus as $menu) {
            $parentId = (int) $menu->idparent;

            while ($parentId !== 0 && ! isset($includedIds[$parentId])) {
                /** @var PqMenu|null $parent */
                $parent = $enabledById->get($parentId);

                if ($parent === null) {
                    break;
                }

                $includedIds[(int) $parent->id] = true;
                $parentId = (int) $parent->idparent;
            }
        }

        return $enabledMenus->filter(
            static fn (PqMenu $menu): bool => isset($includedIds[(int) $menu->id])
        );
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
