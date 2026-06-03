<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PqMenu;

$menu = PqMenu::query()->where('procedimiento', 'pw_detallepedidos')->first();

if ($menu === null) {
    echo "pw_detallepedidos: NO EN pq_menus\n";
    exit(1);
}

echo "pw_detallepedidos: OK\n";
echo "  id={$menu->id}\n";
echo "  text={$menu->text}\n";
echo "  routeName={$menu->routeName}\n";
echo "  idparent={$menu->idparent}\n";
echo "  orden={$menu->orden}\n";
echo "  enabled=".($menu->enabled ? '1' : '0')."\n";
