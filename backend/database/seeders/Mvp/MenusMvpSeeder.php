<?php

namespace Database\Seeders\Mvp;

use App\Models\PqMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

final class MenusMvpSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('paqsuite_mvp.menuItems', []) as $menuItem) {
            $existing = PqMenu::query()
                ->where('procedimiento', $menuItem['procedimiento'])
                ->first();

            if ($existing !== null) {
                if (! $existing->enabled) {
                    $existing->enabled = true;
                    $existing->save();
                }

                continue;
            }

            $nextId = ((int) PqMenu::query()->max('id')) + 1;

            PqMenu::query()->create([
                'id' => $nextId,
                'procedimiento' => $menuItem['procedimiento'],
                'text' => $menuItem['text'],
                'orden' => $menuItem['orden'],
                'enabled' => true,
                'routeName' => $menuItem['routeName'] ?? null,
                'tipo' => 'WEB',
                'tipo_proceso' => 'P',
                'expanded' => false,
                'idparent' => $menuItem['idparent'] ?? 0,
            ]);

            Log::info("pq_menus: insertado procedimiento {$menuItem['procedimiento']} con id {$nextId}.");
        }
    }
}
