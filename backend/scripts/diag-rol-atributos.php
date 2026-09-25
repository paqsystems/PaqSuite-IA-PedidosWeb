<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PqMenu;
use App\Models\PqRol;
use App\Models\PqRolAtributo;
use App\Services\Admin\RoleAttributesService;

$rol = PqRol::query()->where('nombre_rol', 'VendedorAcotado')->first();

if ($rol === null) {
    echo "Rol VendedorAcotado no encontrado\n";
    exit(1);
}

echo 'Rol id='.$rol->id."\n";

$attrs = PqRolAtributo::query()
    ->where('id_rol', $rol->id)
    ->orderBy('procedimiento')
    ->get();

echo 'PQ_RolAtributo count='.$attrs->count()."\n";

foreach ($attrs as $a) {
    echo sprintf(
        "%s repo=%d alta=%d modi=%d baja=%d\n",
        $a->procedimiento,
        (int) $a->permiso_repo,
        (int) $a->permiso_alta,
        (int) $a->permiso_modi,
        (int) $a->permiso_baja,
    );
}

$menus = PqMenu::query()
    ->where('enabled', true)
    ->whereNotNull('procedimiento')
    ->where('procedimiento', '!=', '')
    ->where('procedimiento', 'not like', 'grp_%')
    ->orderBy('orden')
    ->pluck('procedimiento');

echo 'eligibleMenus count='.$menus->count()."\n";

$attrProcs = $attrs->pluck('procedimiento');
$onlyAttrs = $attrProcs->diff($menus);
$onlyMenus = $menus->diff($attrProcs);

echo 'In attrs NOT in eligible menus: '.$onlyAttrs->implode(', ')."\n";
echo 'In eligible menus NOT in attrs (first 15): '.$onlyMenus->take(15)->implode(', ')."\n";

/** @var RoleAttributesService $service */
$service = app(RoleAttributesService::class);
$payload = $service->getForRole((int) $rol->id);

$withAny = collect($payload['items'])->filter(static function (array $item): bool {
    return ($item['permisoAlta'] ?? false)
        || ($item['permisoBaja'] ?? false)
        || ($item['permisoModi'] ?? false)
        || ($item['permisoRepo'] ?? false);
});

echo 'GET items with any flag true: '.$withAny->count()."\n";
foreach ($withAny as $item) {
    echo '  '.$item['procedimiento'].' repo='.(int) $item['permisoRepo']."\n";
}

$user = \App\Models\User::query()->where('codigo', 'vendedor.acotado.mvp')->first();
if ($user !== null) {
    $menu = app(\App\Services\Menu\AuthorizedMenuBuilder::class)->buildForUser($user);
    echo "\nMenu roots: ".implode(', ', array_map(static fn (array $n): string => (string) ($n['procedimiento'] ?? ''), $menu))."\n";
    if (isset($menu[0]['children']) && is_array($menu[0]['children'])) {
        echo 'grp_pedidos children: '.implode(', ', array_map(
            static fn (array $n): string => (string) ($n['procedimiento'] ?? ''),
            $menu[0]['children']
        ))."\n";
    }
}
