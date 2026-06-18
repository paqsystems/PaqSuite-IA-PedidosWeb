<?php

namespace Database\Seeders\Mvp;

use App\Models\PqMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

final class MenusMvpSeeder extends Seeder
{
    public function run(): void
    {
        $menuItems = config('paqsuite_mvp.menuItems', []);
        $temporaryOrder = ((int) PqMenu::query()->max('orden')) + 100;

        foreach ($menuItems as $menuItem) {
            $existing = PqMenu::query()
                ->where('procedimiento', $menuItem['procedimiento'])
                ->first();

            if ($existing !== null) {
                continue;
            }

            $nextId = ((int) PqMenu::query()->max('id')) + 1;

            PqMenu::query()->create([
                'id' => $nextId,
                'procedimiento' => $menuItem['procedimiento'],
                'text' => (string) $menuItem['text'],
                'orden' => $temporaryOrder++,
                'enabled' => true,
                'routeName' => $menuItem['routeName'] ?? null,
                'tipo' => 'WEB',
                'tipo_proceso' => $this->normalizeTipoProceso((string) ($menuItem['tipoProceso'] ?? 'P')),
                'expanded' => false,
                'idparent' => 0,
            ]);

            Log::info("pq_menus: insertado procedimiento {$menuItem['procedimiento']} con id {$nextId}.");
        }

        $menusByProcedimiento = PqMenu::query()
            ->whereIn('procedimiento', array_column($menuItems, 'procedimiento'))
            ->get()
            ->keyBy('procedimiento');

        $children = array_values(array_filter(
            $menuItems,
            static fn (array $menuItem): bool => isset($menuItem['parentProcedimiento']) || isset($menuItem['idparent'])
        ));

        $roots = array_values(array_filter(
            $menuItems,
            static fn (array $menuItem): bool => ! isset($menuItem['parentProcedimiento']) && ! isset($menuItem['idparent'])
        ));

        foreach ([$children, $roots] as $itemsToSync) {
            foreach ($itemsToSync as $menuItem) {
                /** @var PqMenu|null $menu */
                $menu = $menusByProcedimiento->get($menuItem['procedimiento']);

                if ($menu === null) {
                    continue;
                }

                $parentProcedimiento = isset($menuItem['parentProcedimiento'])
                    ? (string) $menuItem['parentProcedimiento']
                    : null;

                $parentId = 0;
                if ($parentProcedimiento !== null && $parentProcedimiento !== '') {
                    /** @var PqMenu|null $parent */
                    $parent = $menusByProcedimiento->get($parentProcedimiento);
                    $parentId = $parent !== null ? (int) $parent->id : 0;
                } elseif (isset($menuItem['idparent'])) {
                    $parentId = (int) $menuItem['idparent'];
                }

                $menu->text = (string) $menuItem['text'];
                $menu->orden = (int) $menuItem['orden'];
                $menu->enabled = true;
                $menu->routeName = $menuItem['routeName'] ?? null;
                $menu->tipo = 'WEB';
                $menu->tipo_proceso = $this->normalizeTipoProceso((string) ($menuItem['tipoProceso'] ?? 'P'));
                $menu->expanded = false;
                $menu->idparent = $parentId;

                if ($menu->isDirty()) {
                    $menu->save();
                    Log::info("pq_menus: actualizado procedimiento {$menuItem['procedimiento']}.");
                }
            }
        }
    }

    private function normalizeTipoProceso(string $tipoProceso): string
    {
        $normalized = strtoupper(trim($tipoProceso));

        return match ($normalized) {
            'G', 'GRUPO' => 'G',
            'P', 'PROCESO' => 'P',
            'I', 'INFORME' => 'I',
            default => substr($normalized, 0, 1) ?: 'P',
        };
    }
}
