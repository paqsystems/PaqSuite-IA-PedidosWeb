<?php

namespace App\Repositories;

use App\Models\PqMenu;
use PaqSuite\LaravelCore\Menu\MenuQueryRepository;

/**
 * Catálogo enabled para GEN-07 validate (labelKey = menu.{menuKey} del config MVP).
 */
final class PedidosWebMenuQueryRepository implements MenuQueryRepository
{
    public function listEnabled(): array
    {
        $menuKeyByProcedimiento = $this->menuKeyByProcedimiento();

        return PqMenu::query()
            ->where('enabled', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(function (PqMenu $row) use ($menuKeyByProcedimiento): array {
                $procedimiento = (string) $row->procedimiento;
                $menuKey = $menuKeyByProcedimiento[$procedimiento] ?? $procedimiento;

                return [
                    'id' => (int) $row->id,
                    'parentId' => (int) ($row->idparent ?? 0) ?: null,
                    'menuKey' => $menuKey,
                    'labelKey' => 'menu.'.$menuKey,
                    'text' => (string) $row->text,
                    'routeName' => $row->routeName,
                    'order' => (int) $row->orden,
                    'procedimiento' => $procedimiento,
                    'processType' => $row->tipo_proceso,
                ];
            })
            ->all();
    }

    /**
     * @return array<string, string> procedimiento => menuKey
     */
    private function menuKeyByProcedimiento(): array
    {
        $items = config('paqsuite_mvp.menuItems', []);
        if (! is_array($items)) {
            return [];
        }

        $map = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $proc = (string) ($item['procedimiento'] ?? '');
            $menuKey = (string) ($item['menuKey'] ?? '');
            if ($proc !== '' && $menuKey !== '') {
                $map[$proc] = $menuKey;
            }
        }

        return $map;
    }
}
